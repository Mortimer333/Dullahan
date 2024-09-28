<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Adapter\Presentation\Event\EventListener\Asset\ListListener;
use Dullahan\Asset\Application\Exception\AssetExistsException;
use Dullahan\Asset\Application\Exception\AssetInvalidNameException;
use Dullahan\Asset\Application\Port\Infrastructure\AssetPersistenceManagerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetMiddlewareInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetSerializerInterface;
use Dullahan\Asset\Application\Port\Presentation\AssetServiceInterface;
use Dullahan\Asset\Domain\Context;
use Dullahan\Asset\Domain\Directory;
use Dullahan\Asset\Domain\File;
use Dullahan\Main\Contract\Marker\UserServiceInterface;

class AssetMiddleware implements AssetMiddlewareInterface
{
    public const CONTROLLER_TYPE = 'controller';

    public function __construct(
        protected AssetPersistenceManagerInterface $assetManager,
        protected UserServiceInterface $userService,
        protected EntityManagerInterface $em,
        protected AssetSerializerInterface $assetSerializer,
        protected AssetServiceInterface $assetService,
    ) {
    }

    public function serialize(int $id): array
    {
        return $this->assetSerializer->serialize($this->assetService->get($id, $this->generateControllerContext()));
    }

    public function retrieve(int $id): array
    {
        return $this->serialize($id);
    }

    public function move(string $from, string $to): array
    {
        $asset = $this->assetService->getByPath($from);
        $asset = $this->assetService->move($asset, $to, $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializer->serialize($asset);
    }

    public function list(array $pagination): array
    {
        $images = [];
        $assets = $this->assetService->list(
            new Context([
                ListListener::LIMIT => $pagination['limit'] ?? null,
                ListListener::OFFSET => $pagination['offset'] ?? null,
                ListListener::FILTER => $pagination['filter'] ?? null,
                ListListener::SORT => $pagination['sort'] ?? null,
                ListListener::JOIN => $pagination['join'] ?? null,
                ListListener::GROUP => $pagination['group'] ?? null,
                ListListener::COUNT => (bool) ($pagination['count'] ?? true),
                Context::TYPE => self::CONTROLLER_TYPE,
            ])
        );
        //        $assets = $this->em->getRepository(Asset::class)->list(
        //            $pagination,
        //            function (QueryBuilder $qb) use ($user) {
        //                $qb->andWhere('p.userData = :userData')
        //                    ->setParameter('userData', $user->getData())
        //                ;
        //            }
        //        );
        foreach ($assets as $asset) {
            $images[] = $this->assetSerializer->serialize($asset);
        }

        return $images;
    }

    public function upload(
        string $name,
        string $path,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array {
        if (!$this->assetService->validName($name, $this->generateControllerContext())) {
            throw new AssetInvalidNameException($name);
        }

        $file = $this->assetService->create(
            new File(
                $path,
                $name,
                $originalName,
                $resource,
                $size,
                $extension,
                $mimeType,
            ),
            $this->generateControllerContext(),
        );
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializer->serialize($file);
    }

    public function folder(string $parent, string $name): array
    {
        if (!$this->assetService->validName($name, $this->generateControllerContext())) {
            throw new AssetInvalidNameException($name);
        }

        $path = rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $name;
        if ($this->assetService->exists($path, $this->generateControllerContext())) {
            throw new AssetExistsException($path);
        }

        $file = $this->assetService->create(
            new Directory($path),
            $this->generateControllerContext(),
        );
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializer->serialize($file);
    }

    public function reupload(
        int $id,
        $resource,
        string $originalName,
        int $size,
        string $extension,
        string $mimeType,
    ): array {
        $asset = $this->assetService->get($id, $this->generateControllerContext());
        // @TODO this should be handled by client or at least give a chance to authorize user from different source
        $user = $this->userService->getLoggedInUser();
        if ($user->getId() != $asset->entity->getOwner()?->getId()) {
            throw new \Exception('Unauthorized access', 401);
        }

        $asset = $this->assetService->replace($asset, new File(
            $asset->structure->path,
            $asset->structure->name,
            $originalName,
            $resource,
            $size,
            $extension,
            $mimeType,
        ), $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());

        return $this->assetSerializer->serialize($asset);
    }

    public function remove(int $id): void
    {
        $this->assetService->remove($this->assetService->get($id), $this->generateControllerContext());
        $this->assetService->flush($this->generateControllerContext());
    }

    protected function generateControllerContext(): Context
    {
        return new Context([
            Context::TYPE => self::CONTROLLER_TYPE,
        ]);
    }
}
