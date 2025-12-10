<?php

namespace App\Tests\Unit;

use App\Engine\WordCountPartWorker;
use PHPUnit\Framework\TestCase;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\Logging\StaticLogger;

class WordCountPartWorkerTest extends TestCase
{
    public function test_TC01_BasicWordCounting(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC01_BasicWordCounting test");
        // TC01
        $textPart = ["the", "fall", "the", "crush", "the", "pain", "the", "fall"];
        $forbidden = [];

        $result = WordCountPartWorker::processPart($textPart, $forbidden);

        $expected = [
            "counts" => [
                "the" => 4,
                "fall" => 2,
                "crush" => 1,
                "pain" => 1
            ], "blocked" => []
        ];

        $this->assertEquals($expected, $result);
        StaticLogger::info("Completed TC01_BasicWordCounting test");
    }

    public function test_TC02_SingleLetterWordsIgnored(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC02_SingleLetterWordsIgnored test");
        // TC02
        $textPart = ["a", "fall", "a", "crush", "a", "pain", "a", "fall"];
        $forbidden = [];

        $result = WordCountPartWorker::processPart($textPart, $forbidden);

        $expected = [
            "counts" => [
                "fall" => 2,
                "crush" => 1,
                "pain" => 1,
            ], "blocked" => []
        ];

        $this->assertEquals($expected, $result);
        StaticLogger::info("Completed TC02_SingleLetterWordsIgnored test");
    }

    public function test_TC03_EmptyArrayReturnsEmpty(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC03_EmptyArrayReturnsEmpty test");

        // TC03
        $textPart = [];
        $forbidden = [];

        $expected = [
            "counts" => [], 
            "blocked" => []
        ];


        $result = WordCountPartWorker::processPart($textPart, $forbidden);

        $this->assertEquals($expected, $result);
        StaticLogger::info("Completed TC03_EmptyArrayReturnsEmpty test");
    }

    public function test_TC04_ForbiddenWordsFiltered(): void
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC04_ForbiddenWordsFiltered test");
        // TC04
        $textPart = ["the", "fall", "the", "crush", "the", "pain", "the", "fall"];
        $forbidden = ["the", "crush"];

        $result = WordCountPartWorker::processPart($textPart, $forbidden);

        $expected = [
            "counts" => [
                "fall" => 2,
                "pain" => 1
            ], 
            "blocked" => ["the", "crush"]
        ];

        $this->assertEquals($expected, $result);

        StaticLogger::info("Completed TC04_ForbiddenWordsFiltered test");
    }
}
