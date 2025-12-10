<?php

namespace App\Tests\Controller;

use PHPUnit\Framework\TestCase;
use App\Controller\WordCloudEngineController;
use Symfony\Component\HttpFoundation\Request;
use Monolog\Logger;
use App\Logging\StaticLogger;
use Psr\Log\LoggerInterface;
use Monolog\Handler\StreamHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

class WordCloudEngineControllerTest extends TestCase
{
    public function test_TC15_IndexHandlesUppercaseWords()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC15_IndexHandlesUppercaseWords test");
        // TC15
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "THE Fall the crush the pain the FALL THE FaLL",
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );

        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(5, $data['data']['totalWords']);

        // Controleer een paar belangrijke woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => 'THE', 'count' => 2],
            ['word' => 'fall', 'count' => 2],
            ['word' => 'FALL', 'count' => 1],
            ['word' => 'crush', 'count' => 1],
            ['word' => 'pain', 'count' => 1],
        ];

        foreach ($expected as $i => $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }
        StaticLogger::info("Completed TC15_IndexHandlesUppercaseWords test");
    }

    public function test_TC16_IndexHandlesUnicodeWords()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC16_IndexHandlesUnicodeWords test");
        // TC16
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "thé café the crush the pain thé café the cafe",
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );
        
        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(5, $data['data']['totalWords']);

        // Controleer de belangrijkste woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => 'thé', 'count' => 2],
            ['word' => 'café', 'count' => 2],
            ['word' => 'crush', 'count' => 1],
            ['word' => 'pain', 'count' => 1],
            ['word' => 'cafe', 'count' => 1],
        ];

        foreach ($expected as $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }
        StaticLogger::info("Completed TC16_IndexHandlesUnicodeWords test");
    }

    public function test_TC17_IndexHandlesNumbersAndAlphanumerics()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC17_IndexHandlesNumbersAndAlphanumerics test");
        // TC17
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "123 1 caf3 123 crush the pain 123 caf3 the cafe",
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );

        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(5, $data['data']['totalWords']);

        // Controleer de belangrijkste woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => '123', 'count' => 3],
            ['word' => 'caf3', 'count' => 2],
            ['word' => 'crush', 'count' => 1],
            ['word' => 'pain', 'count' => 1],
            ['word' => 'cafe', 'count' => 1],
        ];

        foreach ($expected as $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }

        StaticLogger::info("Completed TC17_IndexHandlesNumbersAndAlphanumerics test");
    }

    public function test_TC18_IndexHandlesSymbols()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC18_IndexHandlesSymbols test");
        // TC18
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "the fall@@ ! the - crush! the pain!! the fall@ the fall!",
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );
        
        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(3, $data['data']['totalWords']);

        // Controleer de belangrijkste woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => 'fall', 'count' => 3],
            ['word' => 'crush', 'count' => 1],
            ['word' => 'pain', 'count' => 1],
        ];

        foreach ($expected as $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }

        StaticLogger::info("Completed TC18_IndexHandlesSymbols test");
    }

    public function test_TC19_IndexHandlesApostropheAndHyphen()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC19_IndexHandlesApostropheAndHyphen test");
        // TC19
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "rock'n'roll rock'n'roll test-case test-case",
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );
        
        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(2, $data['data']['totalWords']);

        // Controleer de belangrijkste woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => "rock'n'roll", 'count' => 2],
            ['word' => "test-case", 'count' => 2],
        ];

        foreach ($expected as $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }

        StaticLogger::info("Completed TC19_IndexHandlesApostropheAndHyphen test");
    }

    public function test_TC20_IndexHandlesForbiddenAndPreferredMix()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC20_IndexHandlesForbiddenAndPreferredMix test");
        // TC20
        // Stel request parameters in
        $request = new Request(
            query: [],
            request: 
            [
                'text' => "the fall the crush the pain the fall the fall",
                'maxWords' => 100,
                'forbiddenWords' => ['crush', 'fall'],
                'preferredWords' => ['the', 'fall'],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );

        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        // Controller instantiëren
        $controller = new WordCloudEngineController($loggerMock);

        // Index aanroepen
        /** @var JsonResponse $response */
        $response = $controller->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        $this->assertEquals('ok', $data['status']);

        $this->assertEquals(3, $data['data']['totalWords']);

        // Controleer de belangrijkste woorden en counts
        $cloudWords = $data['data']['cloudWords'];
        $expected = [
            ['word' => 'the', 'count' => 5],
            ['word' => 'fall', 'count' => 3],
            ['word' => 'pain', 'count' => 1],
        ];

        foreach ($expected as $exp) {
            $this->assertContains($exp, $cloudWords, "Expected word {$exp['word']} with count {$exp['count']} not found.");
        }

        StaticLogger::info("Completed TC20_IndexHandlesForbiddenAndPreferredMix test");
    }

    public function test_TC21_IndexPerformanceRandom250kWordsFromFile()
    {
        $logger = new Logger('test');
        $logger->pushHandler(new StreamHandler(__DIR__.'/../../var/log/test.log', 100));
        StaticLogger::set($logger);

        StaticLogger::info("Starting TC21_IndexPerformanceRandom250kWordsFromFile test");
        // TC21
        $wordListFile = __DIR__ . '/../../data/wordlist.txt';

        $this->assertFileExists($wordListFile, "Word list file not found: $wordListFile");

        // Woorden uit bestand inlezen
        $words = file($wordListFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertNotEmpty($words, "Word list file is empty");

        // Maak een grote dataset van 100.000 random woorden
        $targetCount = 100000;
        $randomWords = [];

        for ($i = 0; $i < $targetCount; $i++) {
            $randomWords[] = $words[array_rand($words)];
        }

        $largeText = implode(' ', $randomWords);

        // Mock Request object
        $request = new Request(
            query: [],
            request: 
            [
                'text' => $largeText,
                'maxWords' => 100,
                'forbiddenWords' => [],
                'preferredWords' => [],
            ],
            attributes: [],
            cookies: [],
            files: [],
            server: []
        );

        // Maak een mock van LoggerInterface
        $loggerMock = $this->createMock(LoggerInterface::class);

        // Optioneel: verwacht dat error() niet wordt aangeroepen
        $loggerMock->expects($this->never())
                   ->method('error');

        $controller = new \App\Controller\WordCloudEngineController($loggerMock);

        $start = microtime(true);

        /** @var \Symfony\Component\HttpFoundation\JsonResponse $response */
        $response = $controller->index($request);

        $duration = microtime(true) - $start;

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\JsonResponse::class, $response);

        $data = json_decode($response->getContent(), true);

        // Algemene validatie
        $this->assertEquals('ok', $data['status']);
        $this->assertArrayHasKey('data', $data);

        // De controller mag maximaal 100 woorden retourneren
        $this->assertArrayHasKey('cloudWords', $data['data']);
        $this->assertLessThanOrEqual(
            100,
            count($data['data']['cloudWords']),
            "Output should contain maxWords=100 entries."
        );

        // Performance check (aanpasbaar)
        $this->assertLessThan(
            5,
            $duration,
            "Processing was too slow: {$duration} seconds."
        );
        StaticLogger::info("Completed TC21_IndexPerformanceRandom250kWordsFromFile test in {$duration} seconds.");
    }
}
