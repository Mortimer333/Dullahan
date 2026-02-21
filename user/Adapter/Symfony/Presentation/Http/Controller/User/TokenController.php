<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Http\Controller\User;

use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Port\Application\AccessControlInterface;
use Dullahan\User\Port\Domain\JWTManagerInterface;
use Dullahan\User\Presentation\Http\Response\Token\RefreshTokenResponseDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Token')]
#[Route('/token')]
class TokenController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
    ) {
    }

    #[Route('/refresh', name: 'api_token_refresh', methods: 'PUT')]
    #[SWG\Response(
        description: 'Returns refreshed JWS authentication token',
        content: new Model(type: RefreshTokenResponseDTO::class),
        response: 200
    )]
    public function refresh(
        JWTManagerInterface $jwtService,
        Security $security,
        AccessControlInterface $accessControl,
    ): JsonResponse {
        /** @var User $user */
        $user = $security->getUser();

        $token = $jwtService->createToken($user);
        $payload = $jwtService->validateAndGetPayload($token);

        return $this->httpUtilService->jsonResponse(
            'Token refreshed',
            data: [
                'auth' => $token,
                'csrf' => $accessControl->generateCSRFToken(
                    $payload['session'] ?? throw new AccessDeniedHttpException('Missing session in token payload'),
                ),
            ],
        );
    }
}
