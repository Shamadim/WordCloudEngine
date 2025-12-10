<?php

namespace App\Engine;

use App\Logging\StaticLogger;
/**
 * Worker for processing a part of text: cleaning, counting and filtering.
 * Designed to be called from a parallel runtime closure.
 */

class WordCountPartWorker
{
    public function __construct() {}

    /**
     * Process a text part and return an associative array word => count.
     * Forbidden list should be arrays of lowercase words.
     *
     * @param array<string> $textPart
     * @param array<string> $forbidden
     * @return array {
     *     counts: array<string, int>,
     *     blocked: string[]
     * }
     */
    public static function processPart(array $textPart, array $forbidden = []): array
    {
        $counts = self::countWords($textPart);
        $counts = self::cleanCountResults($counts, $forbidden);
        return $counts;
    }


    /**
     * Count words in the provided (cleaned) text part.
     * Keeps fully-uppercase tokens as-is; otherwise uses lowercase keys.
     *
     * @param string $textPart
     * @return array<string,int>
     */
    private static function countWords(array $part): array
	{
		$wordCounts = [];
		foreach ($part as $word) {
			if ($word === '') continue;
			// Leave all uppercase words intact, otherwise lowercase
			$key = (mb_strtoupper($word, 'UTF-8') === $word ) ? $word : mb_strtolower($word, 'UTF-8');
			if (!array_key_exists($key, $wordCounts)) $wordCounts[$key] = 0;
			$wordCounts[$key]++;
		}
        StaticLogger::debug(json_encode($wordCounts));
		return $wordCounts;
	}

    /**
     * Remove single-letter words and forbidden words from results.
     *
     * @param array $results
     * @param array $forbidden (lowercased)
     * @return array {
    *     counts: array<string, int>,
    *     blocked: string[]
    * }
    */
    private static function cleanCountResults(array $results, array $forbiddenWordList): array
	{
		$out = [];
        $blocked = [];
		foreach ($results as $word => $count) {
			if (mb_strlen($word, 'UTF-8') <= 1) continue;
			if (in_array($word, $forbiddenWordList, true)) {
                $blocked[] = $word;
                continue;
            }
			$out[$word] = $count;
		}

        StaticLogger::debug(json_encode([
            'counts' => $out,
            'blocked' => $blocked
        ]));

		return [
            'counts' => $out,
            'blocked' => $blocked
        ];
	}
}
