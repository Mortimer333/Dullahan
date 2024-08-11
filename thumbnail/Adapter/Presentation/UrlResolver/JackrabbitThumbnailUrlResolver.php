<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Presentation\UrlResolver;

use Dullahan\Thumbnail\Application\Exception\ThumbnailPathCannotBeResolvedException;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailInterface;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailUrlResolverInterface;

/**
 * @TODO maybe move it to separate domain JackrabbitAsset?
 */
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
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException) {
            throw new ThumbnailPathCannotBeResolvedException($thumbnail->getAsset()->getPath());
        }
    }

    public function getUrlPath(ThumbnailInterface $thumbnail): string
    {
        try {
            return $this->router->generate(self::RETRIEVE_PATH_NAME, ['id' => $thumbnail->getId()]);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException) {
            throw new ThumbnailPathCannotBeResolvedException($thumbnail->getAsset()->getPath());
        }
    }
}
