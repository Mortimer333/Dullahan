<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Symfony\Component\HttpFoundation\Response;

class ProjectManagerService
{
    /**
     * @param array<string, array{
     *     class: string,
     * }> $projects
     */
    public function __construct(
        protected array $projects
    ) {
    }

    /**
     * @return array<string, array{
     *     class: string,
     * }>
     */
    public function getProjects(): array
    {
        return $this->projects;
    }

    public function urlSlugNamespaceToClassName(string $project, string $namespace): string
    {
        $context = $this->projects[$project]['class'] ?? throw new \Exception(
            'Not handled project type',
            Response::HTTP_UNPROCESSABLE_ENTITY,
        );
        $context = rtrim($context, '\\') . '\\';
        $namespace = str_replace('-', ' ', trim($namespace));
        $namespace = ucwords($namespace);
        $namespace = str_replace(' ', '\\', $namespace);

        if (!class_exists($context . $namespace)) {
            throw new \Exception(
                'Invalid project or namespace given, selected entity definition doesn\'t exist',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        return $context . $namespace;
    }
}
