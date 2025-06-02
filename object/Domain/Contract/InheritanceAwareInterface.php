<?php

declare(strict_types=1);

namespace Dullahan\Object\Domain\Contract;

use Doctrine\Common\Collections\Collection;

interface InheritanceAwareInterface
{
    public function getId(): ?int;

    public function setParent(?InheritanceAwareInterface $parent): self;

    public function getParent(): ?InheritanceAwareInterface;

    /**
     * @return Collection<int, InheritanceAwareInterface>
     */
    public function getChildren(): Collection;

    public function addChild(InheritanceAwareInterface $child): self;

    public function removeChild(InheritanceAwareInterface $child): self;

    /**
     * Relation path is comma seperated IDs of parents of this child. If null it doesn't have any parents.
     * It helps with retrieving all parents of the entity at once and filling missing/inherited fields.
     */
    public function setRelationPath(?string $RelationPath): self;

    public function getRelationPath(): ?string;

    public function getInherited(string $field): mixed;

    public function getRootId(): ?int;
}
