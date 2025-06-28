<?php

declare(strict_types=1);

namespace Dullahan\Entity\Domain\DefaultAction;

use Dullahan\Entity\Domain\Normalizers\AssetPointerNormalizer;
use Dullahan\Entity\Domain\Normalizers\CacheReferenceReplacerNormalizer;
use Dullahan\Entity\Domain\Normalizers\DateTimeNormalizer;
use Dullahan\Entity\Domain\Normalizers\EnumNormalizer;
use Dullahan\Entity\Domain\Normalizers\InheritedValueNormalizer;
use Dullahan\Entity\Domain\Normalizers\RelationNormalizer;
use Dullahan\Entity\Presentation\Event\Transport\RegisterEntityNormalizer;

class RegisterEntityNormalizersFunctor
{
    public function __construct(
        protected AssetPointerNormalizer $assetPointerNormalizer,
        protected CacheReferenceReplacerNormalizer $cacheReferenceReplacerNormalizer,
        protected DateTimeNormalizer $dateTimeNormalizer,
        protected RelationNormalizer $relationNormalizer,
        protected EnumNormalizer $enumNormalizer,
        protected InheritedValueNormalizer $inheritedValueNormalizer,
    ) {
    }

    public function __invoke(RegisterEntityNormalizer $event): void
    {
        /**
         * Those are our default normalizers for serializing entities.
         * Order matters! We should always start with InheritedValueNormalizer
         * as he retrieves inherited value for serialization if necessary
         * and end with CacheReferenceReplacerNormalizer which replaces objects
         * (end results) with cache references.
         */
        $defaultNormalizers = [
            $this->inheritedValueNormalizer,            // Always first!
            $this->dateTimeNormalizer,
            $this->enumNormalizer,
            $this->assetPointerNormalizer,
            $this->relationNormalizer,
            $this->cacheReferenceReplacerNormalizer,    // Always last!
        ];

        foreach ($defaultNormalizers as $i => $defaultNormalizer) {
            // Setting normalizers in space between equal 10 to give room for additional normalizers
            $event->register($defaultNormalizer, $i * 10);
        }
    }
}
