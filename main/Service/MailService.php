<?php

declare(strict_types=1);

namespace Dullahan\Main\Service;

use Dullahan\Main\Contract\ErrorCollectorInterface;
use Dullahan\Main\Contract\MailServiceInterface;
use Dullahan\User\Domain\Entity\User;
use Dullahan\User\Port\Application\UserManagerServiceInterface;
use Dullahan\User\Port\Application\UserServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @TODO This is a box of nails, all ready to get our hand hurt. We have to remove any Domain related methods
 *   and create a tool MailService not MailBinService
 */
class MailService implements MailServiceInterface
{
    public function __construct(
        protected HttpClientInterface $httpClient,
        protected LoggerInterface $logger,
        protected UserServiceInterface $userService,
        protected UserManagerServiceInterface $userManageService,
        protected ErrorCollectorInterface $errorCollector,
    ) {
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function request(string $method, string $path): ResponseInterface
    {
        // TODO change to use mail service:
        // - create proper mail service and use parameters from config
        // - allow to substitute your own mailing service which must implement given interface
        $client = new MockHttpClient([new MockResponse(
            json_encode([
                'success' => true,
            ]) ?: ''
        )]);

        return $client->request($method, $path);
    }

    public function sendActivationEmail(User $user): ResponseInterface
    {
        return $this->request('POST', 'user/' . $user->getId() . '/send/activation');
    }

    public function sendUpdateEmail(User $user): ResponseInterface
    {
        return $this->request('POST', 'user/' . $user->getId() . '/send/mail/update');
    }

    public function sendUpdatePassword(User $user): ResponseInterface
    {
        return $this->request('POST', 'user/' . $user->getId() . '/send/password/update');
    }

    public function sendResetPassword(User $user): ResponseInterface
    {
        return $this->request('POST', 'user/' . $user->getId() . '/send/password/reset');
    }

    public function sendActivationEmailAndVerify(User $user): ResponseInterface
    {
        return $this->handlRequestError(
            function () use ($user): ResponseInterface {
                return $this->sendActivationEmail($user);
            },
            function () use ($user) {
                if ($user->getId()) {
                    $this->userManageService->remove($user->getId());
                }
            }
        );
    }

    public function sendUpdateEmailAndVerify(User $user): ResponseInterface
    {
        return $this->handlRequestError(
            function () use ($user): ResponseInterface {
                return $this->sendUpdateEmail($user);
            },
            function () use ($user) {
                $this->userManageService->updateNewEmail($user, null);
            }
        );
    }

    public function sendUpdatePasswordAndVerify(User $user): ResponseInterface
    {
        return $this->handlRequestError(
            function () use ($user): ResponseInterface {
                return $this->sendUpdatePassword($user);
            },
            function () use ($user) {
                $this->userManageService->updateNewPassword($user, null);
            }
        );
    }

    public function handleResetPassword(User $user): ResponseInterface
    {
        return $this->handlRequestError(
            function () use ($user): ResponseInterface {
                return $this->sendResetPassword($user);
            }
        );
    }

    protected function handlRequestError(callable $response, ?callable $additional = null): ResponseInterface
    {
        try {
            $response = $response();
            /** @var ResponseInterface $response */
            $jsonResponse = json_decode($response->getContent(), true) ?: [];
        } catch (\Throwable $e) {
            if ($additional) {
                $additional();
            }
            throw $e;
        }

        if (
            200 > $response->getStatusCode()
            || 299 < $response->getStatusCode()
            || !($jsonResponse['success'] ?? false)
        ) {
            if ($additional) {
                $additional();
            }
            if (!isset($jsonResponse['message'])) {
                throw new \Exception('Unexpected error, please contact administrator', 500);
            }
            $this->errorCollector->setErrors($jsonResponse['errors'] ?? []);
            throw new \Exception($jsonResponse['message'], $response->getStatusCode());
        }

        return $response;
    }
}
