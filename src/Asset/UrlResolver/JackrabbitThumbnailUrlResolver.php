<?php

declare(strict_types=1);

namespace Dullahan\Asset\UrlResolver;

use Dullahan\Contract\AssetManager\ThumbnailInterface;
use Dullahan\Contract\AssetManager\ThumbnailUrlResolverInterface;
use Dullahan\Exception\AssetManager\AssetThumbnailPathCannotBeRetrievedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class JackrabbitThumbnailUrlResolver implements ThumbnailUrlResolverInterface
{
    public const RETRIEVE_PATH_NAME = 'api_jackrabbit_thumbnail_image_retrieve';

    public function __construct(
        protected RouterInterface $router,
    ) {
    }

    public function getUrl(ThumbnailInterface $thumbnail): string
    {
        try {
            return $this->router->generate(self::RETRIEVE_PATH_NAME, [
                'id' => $thumbnail->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterExceptio) {
            throw new AssetThumbnailPathCannotBeRetrievedException($thumbnail->getAsset()->getPath());
        }
    }

    public function getUrlPath(ThumbnailInterface $thumbnail): string
    {
        try {
            return $this->router->generate(self::RETRIEVE_PATH_NAME, ['id' => $thumbnail->getId()]);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterExceptio) {
            throw new AssetThumbnailPathCannotBeRetrievedException($thumbnail->getAsset()->getPath());
        }
    }
}
