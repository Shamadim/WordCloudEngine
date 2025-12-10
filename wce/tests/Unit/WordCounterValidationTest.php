<?php

namespace App\Tests\Unit;

use App\Engine\WordCounter;
use InvalidArgumentException;
use TypeError;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use App\Logging\StaticLogger;
use Monolog\Handler\StreamHandler;

class WordCounterValidationTest extends TestCase
{
    public function test_TC05_InvalidTextThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC05_InvalidTextThrowsException test");
        // TC05
        $this->expectException(InvalidArgumentException::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("  ", 100, [], []);

        StaticLogger::info("Completed TC05_InvalidTextThrowsException test");
    }

    public function test_TC06_InvalidMaxWordsTypeThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC06_InvalidMaxWordsTypeThrowsException test");
        // TC06
        $this->expectException(\TypeError::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", "hallo", [], []);
        StaticLogger::info("Completed TC06_InvalidMaxWordsTypeThrowsException test");
    }

    public function test_TC07_MaxWordsAboveMaxThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC07_MaxWordsAboveMaxThrowsException test");
        // TC07
        $this->expectException(InvalidArgumentException::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 10000000, [], []);
        StaticLogger::info("Completed TC07_MaxWordsAboveMaxThrowsException test");
    }

    public function test_TC08_MaxWordsZeroThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC08_MaxWordsZeroThrowsException test");

        // TC08
        $this->expectException(InvalidArgumentException::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 0, [], []);
        StaticLogger::info("Completed TC08_MaxWordsZeroThrowsException test"); 
    }

    public function test_TC09_ForbiddenWordsMustBeArray(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC09_ForbiddenWordsMustBeArray test");

        // TC09
        $this->expectException(\TypeError::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 100, "hallo", []);

        StaticLogger::info("Completed TC09_ForbiddenWordsMustBeArray test");
    }

    public function test_TC10_TooManyForbiddenWordsThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC10_TooManyForbiddenWordsThrowsException test");

        // TC10
        $this->expectException(InvalidArgumentException::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 100, array_fill(0, 15, "x"), []);
        StaticLogger::info("Completed TC10_TooManyForbiddenWordsThrowsException test");
    }

    public function test_TC11_PreferredWordsMustBeArray(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC11_PreferredWordsMustBeArray test");

        // TC11
        $this->expectException(\TypeError::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 100, [], "hallo");

        StaticLogger::info("Completed TC11_PreferredWordsMustBeArray test");
    }

    public function test_TC12_TooManyPreferredWordsThrowsException(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC12_TooManyPreferredWordsThrowsException test");

        // TC12
        $this->expectException(InvalidArgumentException::class);

        $wc = new WordCounter();
        $wc->validateAndSetParameters("the fall", 100, [], array_fill(0, 15, "x"));

        StaticLogger::info("Completed TC12_TooManyPreferredWordsThrowsException test");
    }
}
