<?php

declare(strict_types=1);

namespace Dullahan\User\Domain;

use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Domain\Exception\BadCredentialsException;
use Dullahan\User\Port\Domain\JWTManagerInterface;
use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\AudienceChecker;
use Jose\Component\Checker\ExpirationTimeChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Checker\IssuedAtChecker;
use Jose\Component\Checker\IssuerChecker;
use Jose\Component\Core\AlgorithmManagerFactory;
use Jose\Component\Signature\JWS;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class JWSService extends JWTServiceAbstract implements JWTManagerInterface
{
    public function __construct(
        protected AlgorithmManagerFactory $algorithmManagerFactory,
        protected CacheItemPoolInterface $cache,
        protected BinUtilService $baseUtilService,
        protected HttpUtilService $httpUtilService,
        protected EventDispatcherInterface $eventDispatcher,
    ) {
        $this->validateAlgorithmEnvsExist(); // @TODO maybe move this to compiler pass?
    }

    public function createToken(User $user): string
    {
        $payload = $this->createPayload($user);
        [$signatureKeySet] = $this->getKeys();
        $sigJWK = $signatureKeySet->get('sig-main');

        $signatureAlgorithm = $_ENV['JWT_SINGNATURE_ALGORITHM'];
        $algorithmManager = $this->algorithmManagerFactory->create([$signatureAlgorithm]);

        $jwsBuilder = new JWSBuilder($algorithmManager);

        $payload = json_encode($payload);
        if (!$payload) {
            throw new \InvalidArgumentException('Token payload was improperly formated', 500);
        }

        $jws = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($sigJWK, $this->addRequiredToHeader($user, ['alg' => $signatureAlgorithm]))
            ->build()
        ;

        return (new CompactSerializer())->serialize($jws, 0);
    }

    /**
     * @return array<mixed>
     */
    public function validateAndGetPayload(?string $token): array
    {
        if (!$token) {
            throw new AccessDeniedHttpException('Token authentication is required to access this resource.');
        }

        try {
            $jws = $this->loadAndVerifyToken($token);
            $payload = json_decode($jws->getPayload() ?? '', true);

            if (empty($payload) || !isset($payload['user_id']) || !isset($payload['user'])) {
                throw new \InvalidArgumentException("Token's payload is invalid");
            }

            return $payload;
        } catch (\InvalidArgumentException $e) {
            throw new BadCredentialsException($e->getMessage());
        }
    }

    protected function loadAndVerifyToken(string $token): JWS
    {
        [$signatureKeySet] = $this->getKeys();
        $sigJWK = $signatureKeySet->get('sig-main');

        $signatureAlgorithm = $_ENV['JWT_SINGNATURE_ALGORITHM'];
        $algorithmManager = $this->algorithmManagerFactory->create([$signatureAlgorithm]);

        $jwsVerifier = new JWSVerifier(
            $algorithmManager
        );

        $protectedHeaderOnly = true;
        $headerCheckerManager = new HeaderCheckerManager(
            [
                new AlgorithmChecker([$signatureAlgorithm], $protectedHeaderOnly),
                new AudienceChecker($this->getAudience(), $protectedHeaderOnly),
                new ExpirationTimeChecker($this->httpUtilService->getTokenExpTimeSeconds(), $protectedHeaderOnly),
                new IssuedAtChecker(0, $protectedHeaderOnly),
                new IssuerChecker([$this->getIssuer()], $protectedHeaderOnly),
            ],
            [
                new JWSTokenSupport(),
            ]
        );

        $serializerManager = new JWSSerializerManager([
            new CompactSerializer(),
        ]);

        $jwsLoader = new JWSLoader(
            $serializerManager,
            $jwsVerifier,
            $headerCheckerManager
        );
        /** @var ?int $signature In case of successful verification it will hold index of used signature */
        $signature = null;

        try {
            return $jwsLoader->loadAndVerifyWithKey($token, $sigJWK, $signature);
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Token verification was unsuccessful: ' . $e->getMessage(), 403);
        }
    }
}
