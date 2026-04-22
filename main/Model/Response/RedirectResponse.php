<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response;

class RedirectResponse extends Response
{
    public function __construct(
        public string $url,
        int $status = 302,
        array $headers = [],
    ) {
        parent::__construct(message: 'Redirect to ' . $url, status: $status, headers: $headers);
    }
}
