<?php

declare(strict_types=1);

namespace Dullahan\Main\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Dullahan\Main\Contract\Marker\UserServiceInterface;
use Dullahan\Main\Contract\NotTokenAuthenticatedController;
use Dullahan\Main\Contract\Service\MailServiceInterface;
use Dullahan\Main\Entity\User;
use Dullahan\Main\Event\PostLogin;
use Dullahan\Main\Event\Register\PostRegistration;
use Dullahan\Main\Event\Register\PostValidationRegistration;
use Dullahan\Main\Event\Register\PreRegistration;
use Dullahan\Main\Model\Body\Authentication\ResetPasswordBodyDTO;
use Dullahan\Main\Model\Body\Authentication\ResetPasswordVerifyBodyDTO;
use Dullahan\Main\Model\Body\LoginDto;
use Dullahan\Main\Model\Body\RegisterDto;
use Dullahan\Main\Model\Response\Authentication\ActivationResponseDTO;
use Dullahan\Main\Model\Response\Authentication\LoginResponseDTO;
use Dullahan\Main\Model\Response\Authentication\RegistrationFailedDTO;
use Dullahan\Main\Model\Response\Authentication\RegistrationResponseDTO;
use Dullahan\Main\Model\Response\Authentication\UnauthorizedResponseDTO;
use Dullahan\Main\Model\Response\Manage\UserEmailUpdatedDTO;
use Dullahan\Main\Model\Response\Manage\UserPasswordResetDTO;
use Dullahan\Main\Model\Response\Manage\UserPasswordUpdatedDTO;
use Dullahan\Main\Model\Response\Manage\UserPasswordWasResetDTO;
use Dullahan\Main\Service\JWSService;
use Dullahan\Main\Service\User\UserManageService;
use Dullahan\Main\Service\User\UserValidateService;
use Dullahan\Main\Service\Util\BinUtilService;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\Main\Service\ValidationService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[SWG\Tag('Authentication')]
#[Route(name: 'api_user_')]
class AuthenticationController extends AbstractController implements NotTokenAuthenticatedController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected BinUtilService $baseUtilService,
        protected ValidationService $validationService,
        protected EntityManagerInterface $em,
        protected UserServiceInterface $userService,
        protected UserManageService $userManageService,
        protected UserValidateService $userValidateService,
        protected MailServiceInterface $mailService,
        protected EventDispatcherInterface $eventDispatcher,
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
        $this->eventDispatcher->dispatch(new PreRegistration($request));
        $parameters = $this->httpUtilService->getBody($request);

        $registration = $parameters['register'] ?? [];
        $this->validationService->validateRegistration($registration);
        $this->validationService->validateUserPassword($registration['password'], $registration['passwordRepeat']);
        $this->validationService->validateUserUniqueness($registration['email'], $registration['username']);
        $this->eventDispatcher->dispatch(new PostValidationRegistration($request));

        if ($this->httpUtilService->hasErrors()) {
            throw new \InvalidArgumentException('Registration attempt failed', 400);
        }

        $user = $this->userManageService->create($registration);
        $this->eventDispatcher->dispatch(new PostRegistration($request, $user));

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
    public function login(Request $request, JWSService $jwsService, Security $security): JsonResponse
    {
        /** @var ?User $user */
        $user = $security->getUser();

        if (null === $user) {
            throw new \Exception('Wrong password or username', 401);
        }

        if (!$user->isActivated()) {
            throw new \Exception('User with this email is not activate, you cannot log in using this account', 403);
        }

        $this->eventDispatcher->dispatch(new PostLogin($request, $user));

        $token = $jwsService->createToken($user);

        return $this->httpUtilService->jsonResponse(
            'User authenticated',
            data: [
                'token' => $token,
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
        $this->userValidateService->verifyNewEmail($userId, $token);

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
        $this->userValidateService->verifyNewPassword($userId, $token);

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
        $this->validationService->validateForgottenPassword($forgotten);

        // TODO #recaptcha
        //        $recaptcha = $parameters['recaptcha'] ?? throw new \Exception('Missing reCaptcha token', 400);
        //        if (!is_string($recaptcha)) {
        //            throw new \Exception('reCaptcha token has incorrect type', 400);
        //        }
        //        $this->recaptchaService->verify($recaptcha);

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
        $this->validationService->validateResetPassword($forgotten);

        // TODO #recaptcha
        //        $recaptcha = $parameters['recaptcha'] ?? throw new \Exception('Missing reCaptcha token', 400);
        //        if (!is_string($recaptcha)) {
        //            throw new \Exception('reCaptcha token has incorrect type', 400);
        //        }
        //        $this->recaptchaService->verify($recaptcha);

        $token = $parameters['token'] ?? throw new \Exception('Missing verification token', 400);
        if (!is_string($token)) {
            throw new \Exception('Verification token has incorrect type', 400);
        }

        $user = $this->userValidateService->verifyResetPasswordToken($token);
        /** @var string $password */
        $password = $forgotten['password'] ?? throw new \Exception('Missing password', 500);
        $this->userManageService->resetPassword($user, $password);

        return $this->httpUtilService->jsonResponse('User password was reset successfully');
    }
}
