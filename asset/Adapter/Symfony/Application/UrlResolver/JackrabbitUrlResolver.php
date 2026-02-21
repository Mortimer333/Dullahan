<?php

declare(strict_types=1);

namespace Dullahan\Asset\Adapter\Symfony\Application\UrlResolver;

use Dullahan\Asset\Domain\Asset;
use Dullahan\Asset\Domain\Exception\AssetPathCannotBeRetrievedException;
use Dullahan\Asset\Port\Presentation\AssetUrlResolverInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class JackrabbitUrlResolver implements AssetUrlResolverInterface
{
    public const IMAGE_PATH_NAME = 'api_jackrabbit_image_retrieve';

    public function __construct(
        protected RouterInterface $router,
    ) {
    }

    public function getUrl(Asset $asset): string
    {
        try {
            return $this->router->generate(self::IMAGE_PATH_NAME, [
                'id' => $asset->entity->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL) . '?v=' . ($asset->entity->getModified()?->format('U') ?? '0');
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException $e) {
            throw new AssetPathCannotBeRetrievedException($asset->entity->getFullPath());
        }
    }

    public function getUrlPath(Asset $asset): string
    {
        try {
            return $this->router->generate(self::IMAGE_PATH_NAME, ['id' => $asset->entity->getId()]);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException) {
            throw new AssetPathCannotBeRetrievedException($asset->entity->getFullPath());
        }
    }
}
