<?php

declare(strict_types=1);

namespace Dullahan\Service\Util;

use Symfony\Component\HttpFoundation\Request;

/**
 * Base service with utilization methods.
 */
class BinUtilService
{
    protected static string $projectDir;

    public static function getTmpPath(): string
    {
        $path = rtrim(self::getRootPath(), '/') . '/var/tmp/';
        if (!is_dir($path)) {
            mkdir($path, 0755);
        }

        return $path;
    }

    public static function projectToUrl(string $project): string
    {
        // TODO change project url search to class or configuration - so we don't guess or require any naming schemat
        return $_ENV[strtoupper($project) . '_URL'] ?? throw new \Exception("Project's URL not found", 404);
    }

    public static function logToTest(mixed $data, string $mode = 'w'): void
    {
        $path = self::getRootPath() . '/var/test';
        $file = fopen($path, $mode);
        if ($file) {
            fwrite($file, (json_encode($data, JSON_PRETTY_PRINT) ?: '') . PHP_EOL);
            fclose($file);
        }
    }

    public function saveLastErrorTrace(\Throwable $e, ?Request $request = null): void
    {
        $httpUtilService = new HttpUtilService(new self());
        $path = $this->getRootPath() . '/var/last_error_trace';
        $file = fopen($path, 'w');
        if ($file) {
            if ($request) {
                $content = json_decode($request->getContent(), true);
                fwrite($file, (json_encode($content, JSON_PRETTY_PRINT) ?: '') . PHP_EOL);
            }
            if ($httpUtilService->hasErrors()) {
                fwrite($file, json_encode($httpUtilService->getErrors(), JSON_PRETTY_PRINT) . PHP_EOL);
            }
            fwrite($file, $e->getMessage() . ' => ' . $e->getFile() . ' => ' . $e->getLine() . PHP_EOL);
            fwrite($file, json_encode($e->getTrace(), JSON_PRETTY_PRINT) ?: '');
            fclose($file);
        }
    }

    public static function getRootPath(): string
    {
        if (isset(self::$projectDir)) {
            return self::$projectDir;
        }

        if (isset($_ENV['PROJECT_ROOT'])) {
            return self::$projectDir = $_ENV['PROJECT_ROOT'];
        }

        $r = new \ReflectionObject(new self());

        if (!is_file($dir = $r->getFileName())) {
            throw new \LogicException(
                sprintf('Cannot auto-detect project dir for kernel of class "%s".', $r->name)
            );
        }

        $rootDir = \dirname($dir);
        $dirs = explode('/', $dir);
        array_shift($dirs);
        $dir = '/';
        while (!is_file($dir.'/composer.json')) {
            if ($dir === $rootDir) {
                return self::$projectDir = $rootDir;
            }
            $dir = $dir . (array_splice($dirs, 0, 1)[0] ?? $rootDir) . '/';
        }

        return self::$projectDir = $dir;
    }

    public function isTest(): bool
    {
        return 'test' === $_ENV['APP_ENV'];
    }

    public function isDev(): bool
    {
        $env = ['dev', 'staging'];

        return in_array($_ENV['APP_ENV'], $env, true);
    }

    public function isProduction(): bool
    {
        return 'prod' === $_ENV['APP_ENV'];
    }

    public function generateToken(int $length = 8): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }

    public function generateUniqueToken(string $id, int $length = 8): string
    {
        $token = $this->generateToken($length);
        $idLen = strlen($id);
        $idRand = rand(0, $idLen);
        $outHexScope = ['g', 'h', 'u', 'j', 'k', 'l'];
        $randLetter = $outHexScope[rand(0, count($outHexScope) - 1)];
        // Adding char outside hex scope to avoid generation matching our id in other tokens
        $id = substr($id, 0, $idRand) . $randLetter . substr($id, $idRand);
        $idLen = strlen($id);
        $rand = rand(0, strlen($token) - $idLen);

        return substr($token, 0, $rand) . $id . substr($token, $rand + $idLen);
    }

    public function normalizeName(string $name): string
    {
        return preg_replace('/\s+/', '_', preg_replace(
            '/[^\p{L}\p{N}]/u',
            ' ',
            $name
        ) ?? '') ?? '';
    }

    public static function getCurrentIp(): string
    {
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }
}
