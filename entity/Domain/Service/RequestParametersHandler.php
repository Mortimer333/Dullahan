<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\Service;

use Dullahan\Main\Contract\RequestInterface;

class RequestParametersHandler
{
    public function retrieveDataSet(RequestInterface $request): mixed
    {
        $dataSet = $request->get('dataSet');
        if (is_string($dataSet)) {
            $dataSet = json_decode($dataSet, true) ?: null;
        }

        if (!is_null($dataSet) && !is_array($dataSet)) {
            throw new \InvalidArgumentException('Data Set is invalid', 400);
        }

        return $dataSet;
    }

    public function retrievePagination(RequestInterface $request): mixed
    {
        $pagination = $request->get('pagination', []);
        if (is_string($pagination)) {
            $pagination = json_decode($pagination, true) ?: [];
        }

        if (!is_null($pagination) && !is_array($pagination)) {
            throw new \InvalidArgumentException('Pagination is invalid', 400);
        }

        return $pagination;
    }

    public function retrieveInherit(RequestInterface $request): bool
    {
        return (bool) $request->get('inherit', true);
    }
}
