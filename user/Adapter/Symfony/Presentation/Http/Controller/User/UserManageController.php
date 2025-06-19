<?php

declare(strict_types=1);

namespace Dullahan\User\Adapter\Symfony\Presentation\Http\Controller\User;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Service\Util\HttpUtilService;
use Dullahan\User\Port\Application\MailServiceInterface;
use Dullahan\User\Port\Application\UserManagerServiceInterface;
use Dullahan\User\Port\Application\UserServiceInterface;
use Dullahan\User\Port\Domain\RegistrationValidationServiceInterface;
use Dullahan\User\Port\Domain\UserValidationServiceInterface;
use Dullahan\User\Presentation\Http\Model\Body\Manage\RemoveUserDTO;
use Dullahan\User\Presentation\Http\Model\Body\Manage\UpdateUserDTO;
use Dullahan\User\Presentation\Http\Model\Body\Manage\UpdateUserEmailDTO;
use Dullahan\User\Presentation\Http\Model\Body\Manage\UpdateUserPasswordDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserDetailsDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserEmailVerificationSendDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserPasswordVerificationSendDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserRemovedDTO;
use Dullahan\User\Presentation\Http\Response\Manage\UserUpdatedDTO;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[SWG\Tag('Project User Managment')]
#[Route('/manage', name: 'api_user_manage_')]
class UserManageController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected UserServiceInterface $userService,
        protected UserValidationServiceInterface $userValidateService,
        protected UserManagerServiceInterface $userManageService,
        protected MailServiceInterface $mailService,
        protected RegistrationValidationServiceInterface $registrationValidationService,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    #[Route(
        '/auth',
        name: 'auth',
        methods: 'GET'
    )]
    public function auth(Request $request): Response
    {
        $userPublicId = $request->headers->get('X-User-Token');
        $user = $this->userService->getLoggedInUser();
        if ($user->getData()?->getPublicId() !== $userPublicId) {
            return new Response(status: 401);
        }

        return new Response();
    }

    #[Route('/get', name: 'get', methods: 'GET')]
    #[SWG\Response(
        description: 'User updated',
        content: new Model(type: UserDetailsDTO::class),
        response: 200
    )]
    public function get(): JsonResponse
    {
        $user = $this->userService->getLoggedInUser();

        return $this->httpUtilService->jsonResponse('User details', data: [
            'details' => $this->userService->serialize($user),
            'roles' => $user->getRoles(),
        ]);
    }

    #[Route('/remove', name: 'remove', methods: 'DELETE')]
    #[SWG\RequestBody(attachables: [new Model(type: RemoveUserDTO::class)])]
    #[SWG\Response(
        description: 'User removed',
        content: new Model(type: UserRemovedDTO::class),
        response: 200
    )]
    public function remove(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $user = $this->userService->getLoggedInUser();
        $this->userValidateService->validateUserRemoval($user, $parameters['user']['password'] ?? '');
        $this->userManageService->remove((int) $user->getId(), $parameters['user']['deleteAll'] ?? false);

        return $this->httpUtilService->jsonResponse('User removed successfully');
    }

    #[Route('/update', name: 'update', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: UpdateUserDTO::class)])]
    #[SWG\Response(
        description: 'User updated',
        content: new Model(type: UserUpdatedDTO::class),
        response: 200
    )]
    public function update(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $update = $parameters['update'] ?? [];
        $this->userValidateService->validateUpdateUser($update);
        $user = $this->userService->getLoggedInUser();
        if (isset($update['username'])) {
            $this->registrationValidationService->validateUsernameUniqueness($update['username'], $user);
        }

        if ($this->errorCollector->hasErrors()) {
            throw new \InvalidArgumentException("Couldn't update user details", 400);
        }

        $this->userManageService->update($user, $update);

        return $this->httpUtilService->jsonResponse('User updated successfully', data: [
            'details' => $this->userService->serialize($this->userService->getLoggedInUser()),
        ]);
    }

    #[Route('/update/mail', name: 'update_email', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: UpdateUserEmailDTO::class)])]
    #[SWG\Response(
        description: 'User email verification sent',
        content: new Model(type: UserEmailVerificationSendDTO::class),
        response: 200
    )]
    public function updateEmail(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $update = $parameters['update'] ?? [];

        $user = $this->userService->getLoggedInUser();
        $this->userValidateService->validateUpdateUserMail($update, $user);
        if ($this->errorCollector->hasErrors()) {
            throw new \InvalidArgumentException("Couldn't update user email", 400);
        }

        /** @var string $email */
        $email = $update['email'] ?? throw new \Exception('Missing email', 500);
        $this->userManageService->updateNewEmail($user, $email);
        $this->mailService->sendUpdateEmailAndVerify($user);

        return $this->httpUtilService->jsonResponse('User verification email was sent successfully');
    }

    #[Route('/update/password', name: 'update_password', methods: 'POST')]
    #[SWG\RequestBody(attachables: [new Model(type: UpdateUserPasswordDTO::class)])]
    #[SWG\Response(
        description: 'User password verification sent',
        content: new Model(type: UserPasswordVerificationSendDTO::class),
        response: 200
    )]
    public function updatePassword(Request $request): JsonResponse
    {
        $parameters = $this->httpUtilService->getBody($request);
        $update = $parameters['update'] ?? [];

        $user = $this->userService->getLoggedInUser();
        $this->userValidateService->validatePasswordChange($update, $user);

        /** @var string $newPassword */
        $newPassword = $update['newPassword'] ?? throw new \Exception('', 500);
        $this->userManageService->updateNewPassword($user, $newPassword);

        $this->mailService->sendUpdatePasswordAndVerify($user);

        return $this->httpUtilService->jsonResponse('Change password email was sent successfully');
    }
}
