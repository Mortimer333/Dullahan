<?php

declare(strict_types=1);

namespace Dullahan\Entity\Presentation\Event\Transport;

use Dullahan\Entity\Port\Domain\NormalizerInterface;
use Dullahan\Main\Model\EventAbstract;

/**
 * @template T of object
 *
 * @phpstan-type NormalizerRegistry array<array{
 *     0: NormalizerInterface,
 *     1: number,
 * }>
 */
class RegisterEntityNormalizer extends EventAbstract
{
    /** @var NormalizerRegistry */
    protected array $normalizers = [];

    /**
     * @param T $entity
     */
    public function __construct(
        public readonly object $entity,
    ) {
        parent::__construct();
    }

    public function register(NormalizerInterface $normalizer, ?float $priority = null): bool
    {
        $this->normalizers[] = [$normalizer, $priority ?? \count($this->normalizers)];

        return true;
    }

    /**
     * @param class-string $normalizerClass
     */
    public function unregister(string $normalizerClass): bool
    {
        foreach ($this->normalizers as $i => [$normalizer]) {
            if ($normalizer::class === $normalizerClass) {
                array_splice($this->normalizers, $i, 1);

                return true;
            }
        }

        return false;
    }

    /**
     * @return NormalizerRegistry
     */
    public function getNormalizerRegistry(): array
    {
        return $this->normalizers;
    }

    /**
     * @return NormalizerInterface[]
     */
    public function getSortedNormalizers(): array
    {
        $sorted = $this->normalizers;
        usort($sorted, fn ($aNormalizer, $bNormalizer) => $aNormalizer[1] <=> $bNormalizer[1]);

        return array_column($sorted, 0);
    }
}
