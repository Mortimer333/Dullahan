<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Http\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Contract\EventDispatcherInterface;
use Dullahan\Main\Service\RequestFactory;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Domain\Exception\AccessDeniedHttpException;
use Dullahan\User\Port\Application\AccessControlInterface;
use Dullahan\User\Port\Application\MailServiceInterface;
use Dullahan\User\Port\Application\UserManagerServiceInterface;
use Dullahan\User\Port\Application\UserServiceInterface;
use Dullahan\User\Port\Domain\JWTManagerInterface;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Port\Domain\UserVerifyAndSetServiceInterface;
use Dullahan\User\Presentation\Event\Transport\PostLogin;
use Dullahan\User\Presentation\Event\Transport\PostRegistration;
use Dullahan\User\Presentation\Event\Transport\PreRegistration;
use Dullahan\User\Presentation\Event\Transport\RegistrationValidation;
use Dullahan\User\Presentation\Http\Model\Body\Authentication\ResetPasswordBodyDTO;
use Dullahan\User\Presentation\Http\Model\Body\Authentication\ResetPasswordVerifyBodyDTO;
use Dullahan\User\Presentation\Http\Model\Body\LoginDto;
use Dullahan\User\Presentation\Http\Model\Body\RegisterDto;
use Dullahan\User\Presentation\Http\Response\Authentication\ActivationResponseDTO;
use Dullahan\User\Presentation\Http\Response\Authentication\LoginResponseDTO;
use Dullahan\User\Presentation\Http\Response\Authentication\RegistrationFailedDTO;
use Dullahan\User\Presentation\Http\Response\Authentication\RegistrationResponseDTO;
use Dullahan\User\Presentation\Http\Response\Authentication\UnauthorizedResponseDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserEmailUpdatedDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserPasswordResetDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserPasswordUpdatedDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserPasswordWasResetDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @TODO All of this should be moved to facade service
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[SWG\Tag('Authentication')]
#[Route(name: 'api_user_')]
class AuthenticationController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected BinUtilService $baseUtilService,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected UserManagerServiceInterface $userManageService,
        protected UserValidationServiceInterface $userValidateService,
        protected RegistrationValidationServiceInterface $registrationValidationService,
        protected MailServiceInterface $mailService,
        protected EventDispatcherInterface $eventDispatcher,
        protected UserVerifyAndSetServiceInterface $userVerifyAndSetService,
        protected RequestFactory $requestFactory,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    #[Route('/register', name: 'register', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: RegisterDto::class)])]
    #[SWG\Response(
        description: 'Registration attempt failed',
        content: new Model(type: RegistrationFailedDTO::class),
        response: 400
    )]
    #[SWG\Response(
        description: 'User registered',
        content: new Model(type: RegistrationResponseDTO::class),
        response: 200
    )]
    public function register(Request $request): JsonResponse
    {
        $dullahanRequest = $this->requestFactory->symfonyToDullahanRequest($request);
        $this->eventDispatcher->dispatch(new PreRegistration($dullahanRequest));
        $registration = $this->httpUtilService->getBody($request)['register'] ?? [];
        $registrationValidationEvent = $this->eventDispatcher->dispatch(
            new RegistrationValidation($dullahanRequest, $registration),
        );
        if (!$registrationValidationEvent->isValid()) {
            throw new \InvalidArgumentException('Registration attempt failed', 400);
        }

        $user = $this->userManageService->create($registrationValidationEvent->getRegistration());
        $this->eventDispatcher->dispatch(new PostRegistration($dullahanRequest, $user));

        return $this->httpUtilService->jsonResponse('User registered');
    }

    #[Route('/login', name: 'login', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: LoginDto::class)])]
    #[SWG\Response(
        description: 'Returns the JWS authentication token',
        content: new Model(type: LoginResponseDTO::class),
        response: 200
    )]
    #[SWG\Response(
        description: 'User was unable to log in',
        content: new Model(type: UnauthorizedResponseDTO::class),
        response: 401
    )]
    public function login(
        Request $request,
        JWTManagerInterface $jwtService,
        Security $security,
        AccessControlInterface $accessControl,
    ): JsonResponse {
        /** @var ?User $user */
        $user = $security->getUser();

        if (null === $user) {
            throw new \Exception('Wrong password or username', 401);
        }

        if (!$user->isActivated()) {
            throw new \Exception('User with this email is not activate, you cannot log in using this account', 403);
        }

        $this->eventDispatcher->dispatch(new PostLogin(
            $this->requestFactory->symfonyToDullahanRequest($request),
            $user,
        ));

        $token = $jwtService->createToken($user);
        $payload = $jwtService->validateAndGetPayload($token);

        return $this->httpUtilService->jsonResponse(
            'User authenticated',
            data: [
                'auth' => $token,
                'csrf' => $accessControl->generateCSRFToken(
                    $payload['session'] ?? throw new AccessDeniedHttpException('Missing session in token payload'),
                ),
                'user' => [
                    'details' => $this->userService->serialize($user),
                    'roles' => $user->getRoles(),
                ],
            ],
        );
    }

    #[Route('/{userId<\d+>}/activate/{token}', name: 'activate', methods: 'POST')]
    #[SWG\Response(
        description: 'User activated',
        content: new Model(type: ActivationResponseDTO::class),
        response: 200
    )]
    public function activate(int $userId, string $token): JsonResponse
    {
        $user = $this->userService->get($userId);

        if ($user->getActivationTokenExp() < time()) {
            $this->mailService->sendActivationEmailAndVerify($user);
            throw new \Exception('Token expired, new one was sent to your email', 400);
        }

        $this->userService->activate($userId, $token);

        return $this->httpUtilService->jsonResponse('User activated');
    }

    #[Route('/{userId<\d+>}/verify/{token}/mail', name: 'activate_email', methods: 'POST')]
    #[SWG\Response(
        description: 'User email verification sent',
        content: new Model(type: UserEmailUpdatedDTO::class),
        response: 200
    )]
    public function newEmailVerify(int $userId, string $token): JsonResponse
    {
        $this->userVerifyAndSetService->verifyNewEmail($userId, $token);

        return $this->httpUtilService->jsonResponse('User email verification was successfully');
    }

    #[Route('/{userId<\d+>}/verify/{token}/password', name: 'change_password', methods: 'POST')]
    #[SWG\Response(
        description: 'User password updated',
        content: new Model(type: UserPasswordUpdatedDTO::class),
        response: 200
    )]
    public function newPasswordUpdate(int $userId, string $token): JsonResponse
    {
        $this->userVerifyAndSetService->verifyNewPassword($userId, $token);

        return $this->httpUtilService->jsonResponse('User password updated successful');
    }

    #[Route('/forgotten/password', name: 'forgotten_password', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: ResetPasswordBodyDTO::class)])]
    #[SWG\Response(
        description: 'Sending reset password finished successfully',
        content: new Model(type: UserPasswordResetDTO::class),
        response: 200
    )]
    public function forgottenPassword(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $forgotten = $parameters['forgotten'] ?? [];
        $this->userValidateService->validateForgottenPassword($forgotten);

        $mail = $forgotten['mail'] ?? throw new \Exception('Missing mail', 500);
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $mail]);
        if ($user) {
            $this->mailService->handleResetPassword($user);
        }

        return $this->httpUtilService->jsonResponse('Password reset has finished successfully');
    }

    #[Route('/verify/reset/password', name: 'verify_reset_password', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: ResetPasswordVerifyBodyDTO::class)])]
    #[SWG\Response(
        description: 'User password was reset',
        content: new Model(type: UserPasswordWasResetDTO::class),
        response: 200
    )]
    public function verifyResetPassword(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $forgotten = $parameters['forgotten'] ?? [];
        $this->userValidateService->validateResetPassword($forgotten);

        $token = $parameters['token'] ?? throw new \Exception('Missing verification token', 400);
        if (!is_string($token)) {
            throw new \Exception('Verification token has incorrect type', 400);
        }

        $user = $this->userVerifyAndSetService->verifyResetPasswordToken($token);
        /** @var string $password */
        $password = $forgotten['password'] ?? throw new \Exception('Missing password', 500);
        $this->userManageService->resetPassword($user, $password);

        return $this->httpUtilService->jsonResponse('User password was reset successfully');
    }
}
