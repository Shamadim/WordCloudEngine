<?php

namespace App\Controller;

use App\Engine\WordCounter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use App\Logging\StaticLogger;

class WordCloudEngineController extends AbstractController
{
    public function __construct(LoggerInterface $logger)
    {
        StaticLogger::set($logger);
    }

	 #[Route('/wordcloudengine', name: 'wordcloudengine_index', methods: ['POST'])]
	 
	public function index(Request $request): JsonResponse
	{
        try {
            // Extract data from request
            $text = $request->request->get('text', '');
            $forbiddenWords = (array) $request->request->all('forbiddenWords', []);
            $preferredWords = (array) $request->request->all('preferredWords', []);
            $maxWords = (int)$request->request->get('maxWords', 100);

            // Create an instance of WordCounter and process the text
            $wordCounter = new WordCounter();
            $wordCounter->ValidateAndSetParameters($text, $maxWords, $forbiddenWords, $preferredWords);
            $wordCounter->isParallelAvailable();
            $data = $wordCounter->run();

            return new JsonResponse(['status' => 'ok', 'data' => $data]);
        } catch (\Exception $e) {
            // Log the error for debugging
            StaticLogger::error('WordCloudEngine error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Return a clean JSON error response
            return new JsonResponse([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
	}
}

