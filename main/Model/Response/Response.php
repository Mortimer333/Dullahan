<?php

declare(strict_types=1);

namespace Dullahan\Main\Model\Response;

class Response
{
    /**
     * @param array<mixed>          $data
     * @param array<mixed>          $errors
     * @param array<string, string> $headers
     */
    public function __construct(
        public string $message,
        public bool $success = true,
        public int $status = 200,
        public array $data = [],
        public ?int $limit = null,
        public ?int $offset = null,
        public ?int $total = null,
        public array $errors = [],
        public array $headers = [],
    ) {
    }

    /**
     * @return array{
     *     message: string,
     *     success: bool,
     *     status: int,
     *     data: array<mixed>,
     *     limit: int|null,
     *     offset: int|null,
     *     total: int|null,
     *     errors: array<mixed>,
     * }
     */
    public function toArray(): array
    {
        return [
            'message' => $this->message,
            'success' => $this->success,
            'status' => $this->status,
            'data' => $this->data,
            'limit' => $this->limit,
            'offset' => $this->offset,
            'total' => $this->total,
            'errors' => $this->errors,
            // Do not include headers
        ];
    }
}
