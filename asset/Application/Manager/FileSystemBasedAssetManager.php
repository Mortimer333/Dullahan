<?php

declare(strict_types=1);

namespace Dullahan\Asset\Application\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Asset\Domain\Entity\Asset;
use Dullahan\Main\Service\CacheService;
use Dullahan\Main\Service\ProjectManagerService;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\FileUtilService;
use Dullahan\Main\Service\ValidationService;
use Dullahan\User\Domain\Entity\UserData;
use Dullahan\User\Port\Application\UserServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

// @TODO create a wrapper interface
// @TODO create a wrapper interface

/**
 * @internal Do not use, incomplete manager
 */
class FileSystemBasedAssetManager // implements AssetManagerInterface
{
    //    use \Dullahan\Asset\Application\Trait\SerializeTrait;
    //    use \Dullahan\Asset\Application\Trait\ThumbnailTrait;

    public function __construct(
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected CacheService $cacheService,
        protected ValidationService $validationService,
        protected ProjectManagerService $projectManagerService,
    ) {
    }

    public function get(int $assetId): Asset
    {
        $asset = $this->em->getRepository(Asset::class)->find($assetId);
        if (!$asset) {
            throw new \Exception("Asset with provided ID doesn't exist", 400);
        }

        return $asset;
    }

    public function remove(int $id): void
    {
        $asset = $this->get($id);
        $user = $this->userService->getLoggedInUser();
        if ($asset->getUser()?->getId() !== $user->getId()) {
            throw new \Exception("This asset doesn't belong to you", 403);
        }

        $batchSize = 20;
        foreach ($asset->getPointers() as $i => $pointer) {
            $this->em->persist($pointer);
            $this->em->remove($pointer);
            $entity = $pointer->getEntity();
            if ($entity) {
                $setter = 'set' . ucfirst((string) $pointer->getEntityColumn());
                if (method_exists($entity, $setter)) {
                    $entity->$setter(null);
                    $this->em->persist($entity);
                }
                $this->cacheService->deleteEntityCache($entity, false);
                $this->cacheService->deleteEntityCache($entity, true);
            }

            if (($i % $batchSize) === 0) {
                $this->em->flush();
                $this->em->clear();
            }
        }

        try {
            $asset = $this->get($id);
            $this->em->remove($asset);
            $this->em->flush();
        } catch (\Exception) {
            // If asset is conjoined then he would be removed together with his pointer
        }
    }

    public function updateImage(
        UploadedFile $file,
        Asset $asset,
    ): Asset {
        $this->validationService->validateUploadedFile($file, (int) $asset->getWeight());

        $this->overwriteImage($file, $asset);
        $this->em->persist($asset);
        $this->em->flush();

        return $asset;
    }

    public function uploadImageToFE(
        string $project,
        string $path,
        UploadedFile $file,
        ?string $name = null
    ): Asset {
        $path = rtrim($path, '/') . '/';
        $this->validationService->validateUploadedFile($file);
        $absolute = $this->validateAndRefactorPath($project, $path);

        return $this->saveImage($project, $absolute, $file, $path, $name);
    }

    public function getUniqueAssetName(string $path, string $extension): string
    {
        $name = $baseName = (new BinUtilService())->generateToken();
        $counter = 2;
        while (is_file($path . $name . '.' . $extension)) {
            $name = $baseName . $counter;
            ++$counter;
        }

        return $name;
    }

    protected function saveImage(
        string $project,
        string $path,
        UploadedFile $file,
        string $projectPath,
        ?string $name = null
    ): Asset {
        $mime = $file->getMimeType() ?: '';
        $extension = FileUtilService::ALLOWED_MIME_TYPE[$mime];

        if (!$name) {
            $name = $this->getUniqueAssetName($path, $extension);
        }

        $asset = $this->em->getRepository(Asset::class)->findByPath(
            $_ENV['PATH_IMAGE_FOLDER'] . $projectPath,
            $name,
            $extension
        );
        if ($asset) {
            throw new \Exception('Image with exact same name already exists', 400);
        }

        if (!is_writable($path)) {
            throw new \Exception('New asset directory is not writable', 500);
        }

        $this->stripMeta($file);

        $file->move($path, $name . '.' . $extension);
        if (!is_file($path . $name . '.' . $extension)) {
            throw new \Exception('Uploaded image couldn\'t be saved', 500);
        }

        $size = filesize($path . '/' . $name . '.' . $extension);
        if (false === $size) {
            throw new \Exception("Couldn't read the size of uploaded file", 500);
        }

        $relative = str_replace(
            rtrim($_ENV['PATH_FRONT_END'], '/'),
            '',
            $path
        );

        $user = $this->userService->getLoggedInUser();
        /** @var UserData $userData */
        $userData = $user->getData();

        $asset = new Asset();
        $asset->setMimeType(FileUtilService::extToType($mime))
            ->setName($name)
            ->setExtension($extension)
            ->setProject($project)
            ->setPath($relative)
            ->setWeight($size)
            ->setUserData($userData)
        ;

        return $asset;
    }

    /**
     * @SuppressWarnings(PHPMD.EmptyCatchBlock)
     *
     * @throws \ImagickException
     */
    protected function stripMeta(UploadedFile $file): void
    {
        if (!$this->validationService->isImage($file)) {
            return;
        }

        $imagick = new \Imagick($file->getRealPath());
        try {
            $icc_profile = $imagick->getImageProfile('icc');
        } catch (\ImagickException) {
        }

        try {
            $orientation = $imagick->getImageOrientation();
        } catch (\ImagickException) {
        }

        $imagick->stripImage();

        if (isset($icc_profile)) {
            $imagick->setImageProfile('icc', $icc_profile);
        }

        if (isset($orientation)) {
            $imagick->setImageOrientation($orientation);
        }

        $imagick->writeImage($file->getRealPath());
        $imagick->clear();
    }

    protected function overwriteImage(
        UploadedFile $file,
        Asset $asset,
    ): Asset {
        $extension = FileUtilService::ALLOWED_MIME_TYPE[$file->getMimeType()];
        $oldPath = $asset->getFullPath();
        $asset->setExtension($extension);
        $name = $asset->getName();
        $asset->setModified(new \DateTime());

        if (!is_writable($asset->getFullPathWithoutName())) {
            throw new \Exception('New asset directory is not writable', 500);
        }

        if (is_file($oldPath) && !unlink($oldPath)) {
            throw new \Exception("Couldn't remove old image", 400);
        }

        $this->stripMeta($file);

        $file->move($asset->getFullPathWithoutName(), $name . '.' . $extension);

        $size = filesize($asset->getFullPathWithoutName() . '/' . $name . '.' . $extension);
        if (false === $size) {
            throw new \Exception("Couldn't read the size of uploaded file", 500);
        }
        $asset->setWeight($size);

        $this->replaceThumbnails($asset);

        return $asset;
    }

    protected function validateAndRefactorPath(string $project, string $path): string
    {
        if (0 === strlen($project)) {
            throw new \Exception("Can't upload image without choosing project", 500);
        }

        if (!isset($_ENV['PATH_FRONT_END'])) {
            throw new \Exception('Path to FE is not set', 500);
        }

        if (!isset($_ENV['PATH_IMAGE_FOLDER'])) {
            throw new \Exception('Relative path to image folder is not set', 500);
        }

        $projects = $this->projectManagerService->getProjects();
        $i = 0;
        foreach ($projects as $name => $properties) {
            if ($name == $project) {
                break;
            }

            if ($i + 1 === count($projects)) {
                throw new \Exception("Project's not recognized", 400);
            }
            ++$i;
        }
        $project = rtrim($_ENV['PATH_FRONT_END'], '/') . '/' . rtrim($_ENV['PATH_IMAGE_FOLDER'], '/') . '/'
            . $project . '/';

        if (!is_dir($project)) {
            throw new \Exception("Project's image folder not found", 500);
        }

        if (!is_writable($project)) {
            throw new \Exception("Project's dist is not writeable", 500);
        }

        $project .= trim($path, '/') . '/';
        if (!is_dir($project)) {
            mkdir($project, 0755, true);
        }

        return $project;
    }
}
