<?php

namespace App\Logging;

use Psr\Log\LoggerInterface;

class StaticLogger
{
    private static ?LoggerInterface $logger = null;

    public static function set(LoggerInterface $logger): void
    {
        self::$logger = $logger;
    }

    public static function info(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->info($message, $context);
        }
    }

    public static function debug(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->debug($message, $context);
        }
    }

    public static function error(string $message, array $context = []): void
    {
        if (self::$logger) {
            self::$logger->error($message, $context);
        }
    }
}
