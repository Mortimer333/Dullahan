<?php

declare(strict_types=1);

namespace Dullahan\Main\Contract\Service;

use Dullahan\Main\Entity\User;
use Symfony\Contracts\HttpClient\ResponseInterface;

interface MailServiceInterface
{
    public function sendActivationEmailAndVerify(User $user): ResponseInterface;

    public function sendUpdateEmailAndVerify(User $user): ResponseInterface;

    public function sendUpdatePasswordAndVerify(User $user): ResponseInterface;

    public function handleResetPassword(User $user): ResponseInterface;
}
