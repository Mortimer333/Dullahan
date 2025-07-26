<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Dullahan\Main\Contract\RequestInterface;
use Dullahan\Main\Model\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

class RequestFactory
{
    public function symfonyToDullahanRequest(SymfonyRequest $request): RequestInterface
    {
        $files = [];
        /**
         * @var string       $key
         * @var UploadedFile $file
         */
        foreach ($request->files->all() as $key => $file) {
            $files[$key] = $file->openFile();
        }

        return new Request(
            $request->isSecure(),
            $request->getHost(),
            $request->getPathInfo(),
            $request->getMethod(),
            $request,
            $request->getContent(),
            $request->headers->all(),
            $request->query->all(),
            $request->cookies->all(),
            $files,
            $request->attributes->all(),
            $this->getSymfonyBody($request),
        );
    }

    /**
     * @return array<mixed>
     */
    protected function getSymfonyBody(SymfonyRequest $request): array
    {
        if (!empty($request->request->all())) {
            return $request->request->all();
        }

        $json = json_decode($request->getContent(), true);
        if (is_array($json)) {
            return $json;
        }

        return [];
    }
}
