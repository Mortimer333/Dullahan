<?php

declare(strict_types=1);

namespace Dullahan\Thumbnail\Adapter\Presentation\UrlResolver;

use Dullahan\Thumbnail\Application\Exception\ThumbnailPathCannotBeResolvedException;
use Dullahan\Thumbnail\Application\Port\Presentation\ThumbnailUrlResolverInterface;
use Dullahan\Thumbnail\Domain\Thumbnail;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class JackrabbitThumbnailUrlResolver implements ThumbnailUrlResolverInterface
{
    public const RETRIEVE_PATH_NAME = 'api_jackrabbit_thumbnail_image_retrieve';

    public function __construct(
        protected RouterInterface $router,
    ) {
    }

    public function getUrl(Thumbnail $thumbnail): string
    {
        try {
            return $this->router->generate(self::RETRIEVE_PATH_NAME, [
                'id' => $thumbnail->entity->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException) {
            throw new ThumbnailPathCannotBeResolvedException($thumbnail->structure->path);
        }
    }

    public function getUrlPath(Thumbnail $thumbnail): string
    {
        try {
            return $this->router->generate(self::RETRIEVE_PATH_NAME, ['id' => $thumbnail->entity->getId()]);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterException) {
            throw new ThumbnailPathCannotBeResolvedException($thumbnail->structure->path);
        }
    }
}
