<?php

declare(strict_types=1);

namespace Dullahan\User\Domain;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Presentation\Event\Transport\JWTHeaderCreate;
use Dullahan\User\Presentation\Event\Transport\JWTPayloadCreate;
use Jose\Component\Core\JWKSet;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;

/**
 * @phpstan-import-type TokenHeader from \Dullahan\User\Presentation\Event\Transport\JWTHeaderCreate
 */
abstract class JWTServiceAbstract
{
    public const AUDIENCE = 'Users';
    public const ISSUER = 'Dullahan';

    protected CacheItemPoolInterface $cache;
    protected BinUtilService $baseUtilService;
    protected HttpUtilService $httpUtilService;
    protected EventDispatcherInterface $eventDispatcher;

    /**
     * @return array<JWKSet>
     */
    public function getKeys(): array
    {
        if (!isset($_ENV['JWT_KEYS_ENCRYPTION']) || !isset($_ENV['JWT_KEYS_SIGNATURE'])) {
            throw new \InvalidArgumentException('JWT keys are not set in environment', 500);
        }
        $signatureKeySet = JWKSet::createFromJson($_ENV['JWT_KEYS_SIGNATURE']);
        $encryptionKeySet = JWKSet::createFromJson($_ENV['JWT_KEYS_ENCRYPTION']);

        if (!$signatureKeySet->has('sig-main') || !$encryptionKeySet->has('enc-main')) {
            throw new \InvalidArgumentException("JWT keys don't have required IDs set", 500);
        }

        return [
            $signatureKeySet,
            $encryptionKeySet,
        ];
    }

    protected function getLastJTIKey(): string
    {
        return 'jwt-jti';
    }

    /**
     * @param array<mixed> $header
     *
     * @return TokenHeader
     */
    public function addRequiredToHeader(User $user, array $header): array
    {
        /** @var TokenHeader $header */
        $header = array_merge([
            'alg' => $header['alg'] ?? throw new \Exception('Missing algorithm header in token', 500),
            'jti' => $header['jti'] ?? $this->createJTI((int) $user->getId()),
            'iss' => $header['iss'] ?? $this->getIssuer(),
            'aud' => $header['aud'] ?? $this->getAudience(),
            'iat' => $header['iat'] ?? time(),
            'nbf' => $header['nbf'] ?? time(),
            'exp' => $header['exp'] ?? time() + $this->httpUtilService->getTokenExpTimeSeconds(),
        ], $header);

        return $this->eventDispatcher->dispatch(new JWTHeaderCreate($header, $user))->getHeader();
    }

    public function createJTI(int $userId): string
    {
        return 'api_' . $this->baseUtilService->generateUniqueToken((string) $userId);
    }

    public function validateAlgorithmEnvsExist(): void
    {
        if (
            !isset($_ENV['JWT_SINGNATURE_ALGORITHM'])
            || !isset($_ENV['JWT_ENCRYTPION_ALGORITHM'])
            || !isset($_ENV['JWT_CONTENT_ENCRYTPION_ALGORITHM'])
        ) {
            throw new \InvalidArgumentException('JWT algorithms are not set in environment', 500);
        }
    }

    /**
     * @return array<mixed>
     */
    protected function createPayload(User $user): array
    {
        return $this->eventDispatcher->dispatch(new JWTPayloadCreate([
            'user' => $user->getUserIdentifier(),
            'user_id' => $user->getId(),
            'session' => Uuid::uuid7()->toString(),
        ], $user))->getPayload();
    }

    protected function getIssuer(): string
    {
        return $_ENV['JWT_ISSUER'] ?? self::ISSUER;
    }

    protected function getAudience(): string
    {
        return $_ENV['JWT_AUDIENCE'] ?? self::AUDIENCE;
    }
}
