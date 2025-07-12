<?php

declare(strict_types=1);

namespace Dullahan\Entity\Adapter\Symfony\Domain;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Dullahan\Entity\Domain\Enum\FieldTypeEnum;
use Dullahan\Entity\Port\Application\EntityDefinitionManagerInterface;
use Dullahan\Entity\Port\Application\EntityPersistManagerInterface;
use Dullahan\Entity\Port\Application\EntityRetrievalManagerInterface;
use Dullahan\Entity\Port\Domain\EntityHydrationInterface;
use Dullahan\Entity\Port\Domain\ManageableInterface;
use Dullahan\Entity\Port\Interface\EntityRepositoryInterface;
use Dullahan\Main\Service\EditorJsService;
use Dullahan\Main\Service\Helper\CastHelper;
use Dullahan\User\Port\Application\UserServiceInterface;

/**
 * @TODO This class should work similarly to serializer - importing hydration classes and using them based on the
 *      field type
 */
class EntityHydrationService implements EntityHydrationInterface
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected EditorJsService $editorJsService,
        protected EntityRetrievalManagerInterface $entityRetrievalManager,
        protected UserServiceInterface $userService,
        protected EntityPersistManagerInterface $entityPersistManager,
        protected EntityDefinitionManagerInterface $entityDefinitionManager,
        protected EntityUtilService $entityUtilService, // @TODO replace when update EDD approach is implemented
    ) {
    }

    /**
     * @param class-string             $class
     * @param array<int|string, mixed> $payload
     * @param array<string, mixed>     $definitions
     */
    public function hydrate(string $class, object $entity, array $payload, array $definitions): void
    {
        $meta = (array) $this->em->getClassMetadata($class);
        $mappings = $meta['associationMappings'];
        $fields = $meta['fieldMappings'];
        $this->setMappings($entity, $mappings, $payload, $definitions);
        foreach ($fields as $fieldName => $field) {
            $definition = $definitions[$fieldName] ?? '';
            // field was not attributed with Field Attribute
            if (empty($definition)) {
                continue;
            }
            $setter = 'set' . ucfirst($fieldName);
            if ($definition['auto'] ?? false) {
                $entity->$setter(forward_static_call(...$definition['auto']));
                continue;
            }

            if (!array_key_exists($fieldName, $payload)) {
                continue;
            }

            if ($definition['type'] === FieldTypeEnum::RICH->value) {
                $setterParsed = $setter . 'Parsed';
                if (!method_exists($entity, $setterParsed)) {
                    throw new \Exception(sprintf('Class `%s` is missing `%s`', $entity::class, $setter), 500);
                }

                if (empty($payload[$fieldName]) || empty(json_decode($payload[$fieldName], true))) {
                    $content = null;
                } else {
                    $content = $this->editorJsService->sanitize(json_decode($payload[$fieldName], true));
                }
                $entity->$setter(json_encode($content) ?: null);
                $entity->$setterParsed($this->editorJsService->parse($content ?? []));
            } elseif (
                $definition['type'] === FieldTypeEnum::ENUM->value
                || FieldTypeEnum::ENUM === $definition['type']
            ) {
                $enum = $definition['enum'] ?? throw new \Exception(
                    sprintf('Missing enum value on "%s" of "%s"', $fieldName, $entity::class),
                    500,
                );
                if (!enum_exists($enum)) {
                    throw new \Exception(sprintf('Provided enum %s doesn\'t exists', $enum), 500);
                }

                $value = forward_static_call([$enum, 'tryFrom'], $payload[$fieldName]); // @phpstan-ignore-line
                if (is_null($value)) {
                    throw new \Exception('Value provided for enum was not filtered properly', 500);
                }
                $entity->$setter($value);
            } else {
                $entity->$setter(CastHelper::cast($payload[$fieldName], $field['type']));
            }
        }
    }

    /**
     * @param array<int|string, mixed>            $payload
     * @param array<string, array<string, mixed>> $mappings
     * @param array<mixed>                        $definitions
     */
    protected function setMappings(object $entity, array $mappings, array $payload, array $definitions): void
    {
        foreach ($mappings as $field => $mapping) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $definition = $definitions[$field] ?? '';
            $targetEntity = $mapping['targetEntity'] ?? throw new \Exception('Missing target entity', 500);
            $type = $mapping['type'] ?? throw new \Exception('Missing relation type', 500);
            $fieldName = $mapping['fieldName'] ?? throw new \Exception('Missing relation field name', 500);
            match ($type) {
                ClassMetadata::ONE_TO_MANY, ClassMetadata::MANY_TO_MANY => $this->setMany(
                    $targetEntity,
                    sprintf('Not all chosen %s were found, revalidate sent IDs', $fieldName),
                    $payload[$field],
                    $entity,
                    $definition,
                    $fieldName,
                ),
                ClassMetadata::ONE_TO_ONE, ClassMetadata::MANY_TO_ONE => $this->setOne(
                    $targetEntity,
                    is_array($payload[$field]) ? $payload[$field] : CastHelper::cast($payload[$field], 'integer'),
                    $entity,
                    $fieldName,
                    $definition,
                ),
                default => throw new \Exception(sprintf('Undefined relation type %s', $type), 500),
            };
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param array<mixed>|int|null $item
     * @param array<mixed>|string   $definition
     *
     * @throws \Exception
     */
    protected function setOne(
        string $class,
        int|array|null $item,
        object $entity,
        string $name,
        array|string $definition
    ): void {
        if (!class_exists($class)) {
            throw new \Exception(sprintf("Class %s doesn't exist", $class), 500);
        }

        $setter = 'set' . ucfirst($name);

        if (is_null($item)) {
            $entity->$setter(null);

            return;
        }

        if (is_int($item)) {
            $relative = $this->entityRetrievalManager->getRepository($class)?->find($item);
            if (!$relative) {
                throw new \Exception(ucfirst($name) . ' not found', 404);
            }
        } else {
            $relative = $this->handleNestedEntity($class, $item);
        }

        $this->validateRelationOwnership($relative, $name);

        $entity->$setter($relative);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param class-string        $class
     * @param array<int|mixed>    $newCollection
     * @param array<mixed>|string $definition
     */
    protected function setMany(
        string $class,
        string $notFoundError,
        ?array $newCollection,
        object $entity,
        array|string $definition,
        ?string $name = null,
    ): void {
        if (!$name) {
            $name = explode('\\', $class);
            $name = end($name);
        }
        $name = ucfirst($name);
        $remover = 'remove' . $this->entityDefinitionManager->singularize($definition, $name);
        $adder = 'add' . $this->entityDefinitionManager->singularize($definition, $name);
        $getter = 'get' . $this->entityDefinitionManager->pluralize($definition, $name);
        if (!method_exists($entity, $adder) || !method_exists($entity, $getter)) {
            throw new \Exception(
                sprintf(
                    "Class %s doesn't have method to retrieve (%s) or add (%s) new entities",
                    $entity::class,
                    $getter,
                    $adder,
                ),
                500
            );
        }

        $collection = $entity->$getter();
        if (!$collection instanceof Collection) {
            throw new \Exception(
                sprintf("Classes %s method %s doesn't return collection", $entity::class, $getter),
                500
            );
        }

        $newCollection ??= [];
        $repo = $this->entityRetrievalManager->getRepository($class);
        if (!$repo) {
            throw new \Exception('Entity repository not found', 404);
        }
        $entities = $this->gatherAllEntities($newCollection, $class, $repo);
        if (count($entities) != count($newCollection)) {
            throw new \Exception($notFoundError, 404);
        }

        foreach ($collection as $element) {
            $entity->$remover($element);
        }

        foreach ($entities as $item) {
            $this->validateRelationOwnership($item, $name);
            $entity->$adder($item);
        }
    }

    /**
     * @template T of object
     *
     * @param array<mixed>                 $newCollection
     * @param class-string<T>              $class
     * @param EntityRepositoryInterface<T> $repo
     *
     * @return array<object>
     */
    protected function gatherAllEntities(array $newCollection, string $class, EntityRepositoryInterface $repo): array
    {
        $ids = [];
        $entities = [];
        foreach ($newCollection as $item) {
            if (is_int($item)) {
                $ids[] = $item;
                continue;
            }

            if (is_array($item)) {
                $entities[] = $this->handleNestedEntity($class, $item);
            }
        }

        return array_merge($entities, $repo->findBy(['id' => $ids]));
    }

    /**
     * @param array<string, mixed> $payload
     * @param class-string         $class
     */
    protected function handleNestedEntity(string $class, array $payload): object
    {
        $id = $payload['id'] ?? null;
        unset($payload['id']);
        if ($id) {
            return $this->entityUtilService->update($class, (int) $id, $payload);
        }

        return $this->entityPersistManager->create($class, $payload, false);
    }

    protected function validateRelationOwnership(object $item, string $name): void
    {
        if (
            $item instanceof ManageableInterface
            && !$item->isOwner($this->userService->getLoggedInUser())
        ) {
            throw new \Exception(
                sprintf("In `%s` you are trying to add entity you don't own", ucfirst($name)),
                400
            );
        }
    }
}
