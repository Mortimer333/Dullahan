<?php

declare(strict_types=1);

namespace Dullahan\Main\Asset\UrlResolver;

use Dullahan\Main\Contract\AssetManager\AssetInterface;
use Dullahan\Main\Contract\AssetManager\AssetUrlResolverInterface;
use Dullahan\Main\Exception\AssetManager\AssetPathCannotBeRetrievedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class JackrabbitUrlResolver implements AssetUrlResolverInterface
{
    public const IMAGE_PATH_NAME = 'api_jackrabbit_image_retrieve';

    public function __construct(
        protected RouterInterface $router,
    ) {
    }

    public function getUrl(AssetInterface $asset): string
    {
        try {
            return $this->router->generate(self::IMAGE_PATH_NAME, [
                'id' => $asset->getId(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterExceptio) {
            throw new AssetPathCannotBeRetrievedException($asset->getPath());
        }
    }

    public function getUrlPath(AssetInterface $asset): string
    {
        try {
            return $this->router->generate(self::IMAGE_PATH_NAME, ['id' => $asset->getId()]);
        } catch (RouteNotFoundException|MissingMandatoryParametersException|InvalidParameterExceptio) {
            throw new AssetPathCannotBeRetrievedException($asset->getPath());
        }
    }
}
