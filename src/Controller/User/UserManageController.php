<?php

declare(strict_types=1);

namespace Dullahan\Controller\User;

use Dullahan\Contract\Service\MailServiceInterface;
use Dullahan\Model\Body\Manage\RemoveUserDTO;
use Dullahan\Model\Body\Manage\UpdateUserDTO;
use Dullahan\Model\Body\Manage\UpdateUserEmailDTO;
use Dullahan\Model\Body\Manage\UpdateUserPasswordDTO;
use Dullahan\Model\Response\Manage\UserDetailsDTO;
use Dullahan\Model\Response\Manage\UserEmailVerificationSendDTO;
use Dullahan\Model\Response\Manage\UserPasswordVerificationSendDTO;
use Dullahan\Model\Response\Manage\UserRemovedDTO;
use Dullahan\Model\Response\Manage\UserUpdatedDTO;
use Dullahan\Service\User\UserManageService;
use Dullahan\Service\User\UserValidateService;
use Dullahan\Service\UserService;
use Dullahan\Service\Util\HttpUtilService;
use Dullahan\Service\ValidationService;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[SWG\Tag('Project User Managment')]
#[Route('/manage', name: 'api_user_manage_')]
class UserManageController extends AbstractController
{
    public function __construct(
        protected HttpUtilService $httpUtilService,
        protected UserService $userService,
        protected UserValidateService $userValidateService,
        protected UserManageService $userManageService,
        protected ValidationService $validationService,
        protected MailServiceInterface $mailService,
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
        return $this->httpUtilService->jsonResponse('User details', data: [
            'details' => $this->userService->serialize($this->userService->getLoggedInUser(), true),
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
        $this->validationService->validateUpdateUser($update);
        $user = $this->userService->getLoggedInUser();
        if (isset($update['username'])) {
            $this->validationService->validateUsernameUniqueness($update['username'], $user);
        }

        if ($this->httpUtilService->hasErrors()) {
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
        $this->validationService->validateUpdateUserMail($update, $user);
        if ($this->httpUtilService->hasErrors()) {
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
        $this->validationService->validatePasswordChange($update, $user);

        /** @var string $newPassword */
        $newPassword = $update['newPassword'] ?? throw new \Exception('', 500);
        $this->userManageService->updateNewPassword($user, $newPassword);

        $this->mailService->sendUpdatePasswordAndVerify($user);

        return $this->httpUtilService->jsonResponse('Change password email was sent successfully');
    }
}
