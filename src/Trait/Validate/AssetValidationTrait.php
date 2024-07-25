<?php

declare(strict_types=1);

namespace Dullahan\Trait\Validate;

use Dullahan\Entity\Asset;
use Dullahan\Entity\Settings;
use Dullahan\Entity\UserData;
use Dullahan\Service\Util\FileUtilService;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @deprecated This kind of validation shouldn't be done internally
 */
trait AssetValidationTrait
{
    public function isImage(UploadedFile $file): bool
    {
        return in_array($file->getMimeType(), FileUtilService::IMAGE_MIME_TYPE);
    }

    public function validateUploadedFile(UploadedFile $file, int $updateImageSize = 0): void
    {
        $filesize = filesize($file->getRealPath());
        if ($filesize > 5 * (10 ** 6)) {
            throw new \Exception('Uploaded file is too big, 5 Mb is the limit', 400);
        }

        $user = $this->userService->getLoggedInUser();
        /** @var UserData $userData */
        $userData = $user->getData();
        $limit = (int) $userData->getFileLimitBytes();

        $system = $this->em->getRepository(Settings::class)->findOneBy(['name' => 'system']);
        if (!$system) {
            throw new \Exception('System settings were not found', 500);
        }
        [
            Settings::SYSTEM['takenSpacePrecent'] => $taken,
            Settings::SYSTEM['maxTakenSpacePrecent'] => $maxTaken
        ] = $system->getData();
        if ($taken >= $maxTaken) {
            throw new \Exception(
                'Not enough space! You cannot upload your asset because server lacks space to save it.'
                    . ' Please contact administrator - contact.boardmeister@gmail.com!',
                500
            );
        }

        $currentTakenSpace = $this->em->getRepository(Asset::class)->getTakenSpace($userData);
        // Skipping creators, they can use all the data on server we have
        if ($currentTakenSpace + $filesize - $updateImageSize > $limit) {
            throw new \Exception(
                sprintf(
                    'Asset upload was terminated because you have exceeded your file limit of %s by %s. '
                    . 'Try deleting unused assets from your account or contact administration about possible additional'
                    . ' space on server by sending an email to contact.boardmeister@gmail.com.',
                    FileUtilService::humanFilesize($limit),
                    FileUtilService::humanFilesize($currentTakenSpace + $filesize - $updateImageSize - $limit),
                ),
                400
            );
        }

        $mime = $file->getMimeType();

        if (!$mime) {
            throw new \Exception(
                sprintf(
                    'Uploaded file has incorrect format. Allowed extensions: %s',
                    implode(', ', array_values(FileUtilService::ALLOWED_MIME_TYPE))
                ),
                400
            );
        }

        if ('text/plain' == $mime && FileUtilService::isJson($file->getContent())) {
            $mime = 'application/json';
        }

        FileUtilService::ALLOWED_MIME_TYPE[$mime] ?? throw new \Exception(
            sprintf(
                'Uploaded file has incorrect format. Allowed extensions: %s',
                implode(', ', array_values(FileUtilService::ALLOWED_MIME_TYPE)),
            ),
            400
        );
    }
}
