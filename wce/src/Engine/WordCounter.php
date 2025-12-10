<?php

namespace App\Engine;

use InvalidArgumentException;
use App\Logging\StaticLogger as Log;

/**
 * WordCounter: splits text, counts words (optionally in parallel), and formats results.
 */
class WordCounter
{
	private array $forbiddenWordList = [];
	private array $forbiddenWordsBlocked = [];
	private array $preferredWordList = [];
	private int $maxWords = 100;
	private string $text = '';
	private int $textLength = 0;
	private bool $runParallel = false;
	private bool $ready = false;

	private array $dutchStopwords = [
		'aan','achter','af','al','als','altijd','ander','anderen','bij','binnen','buiten','daar',
		'daardoor','daarin','daarnaast','daarvan','dan','dat','de','deze','die','dit','door','doen',
		'daarom','dus','echter','een','eens','enkele','er','geen','genoeg','gehad','ge','geweest',
		'gaan','gaat','gij','het','hier','hiermee','hiervoor','hiervan','hij','hoe','hun','indien','ik',
		'in','is','je','jij','jouw','jullie','kan','kon','kunnen','kwam','maar','mag','me',
		'meer','meest','men','met','mij','mijn','minder','mits','moet','moeten','mocht','na','naar',
		'naast','niet','niets','nog','nooit','nu','omdat','om','onder','ons','onze','ooit','of',
		'ook','op','ofwel','over','reeds','rond','samen','sinds','soms','steeds','te','tegen',
		'tenzij','toch','toen','tot','tussen','vanuit','van','vanaf','vele','veel','velen','verder',
		'via','vaak','voordat','volgens','voor','waarom','waarvan','waarop','waren','wat','we',
		'weer','weg','wegens','wel','welke','werd','werden','wie','wij','wil','wilde','willen',
		'word','wordt','worden','zonder','zou','zouden','zodat','zulke','zelfs','zelf'
	];

	private array $englishStopwords = [
		'a','about','above','after','again','against','all','also','am','an','and','any','are',
		"aren't",'as','at','be','because','been','before','being','below','between','both','but','by',
		'can',"can't",'could',"couldn't",'did',"didn't",'do','does',"doesn't",'doing',"don't",'down',
		'during','each','few','for','from','further','had',"hadn't",'has',"hasn't",'have',"haven't",
		'having','he',"he'd","he'll","he's",'her','here',"here's",'hers','herself','him','himself',
		'his','how',"how's",'i',"i'd","i'll","i'm","i've",'if','in','into','is',"isn't",'it',"it's",'its',
		'itself',"let's",'let', 'me','more','most',"mustn't",'my','myself','no','nor','not','of','off','on',
		'once','only','or','other','ought','our','ours','ourselves','out','over','own','same',"shan't",
		'she',"she'd","she'll","she's",'should',"shouldn't",'so','some','such','than','that',"that's",
		'the','their','theirs','them','themselves','then','there',"there's",'these','they',"they'd",
		"they'll","they're","they've",'this','those','through','to','too','under','until','up','very',
		'was',"wasn't",'we',"we'd","we'll","we're","we've",'were',"weren't",'what',"what's",'when',
		"when's",'where',"where's",'which','while','who',"who's",'whom','why',"why's",'with',"won't",
		'would',"wouldn't",'you',"you'd","you'll","you're","you've",'your','yours','yourself','yourselves'
	];

	/**
	 * Determine whether parallel processing is available.
	 *
	 * @return boolean
	 */
	protected function parallelClassExists(): bool
    {
        return class_exists('\parallel\Runtime');
    }

	public function __construct()
	{
		// defaults already set on properties
	}

	/**
	 * Determine whether parallel processing is available.
	 *
	 * @return void
	 */

	public function isParallelAvailable(): void
	{
		$this->runParallel = $this->parallelClassExists();
	}

	/**
	 * Determine whether parallel processing is active.
	 *
	 * @return boolean
	 */

	public function isParallelActive(): bool
	{
		return $this->runParallel;
	}

	/**
	 * Validate inputs and set internal parameters.
	 *
	 * @param string $text
	 * @param int $maxWords
	 * @param array $forbiddenWords
	 * @param array $preferredWords
	 * @return void
	 */
	public function ValidateAndSetParameters(string $text, int $maxWords = 100, array $forbiddenWords = [], array $preferredWords = []): void
	{
		Log::info("WordCounter: validation start", [
            'text_length' => strlen($text),
			'max_words' => $maxWords,
			'forbidden_count' => count($forbiddenWords),
			'preferred_count' => count($preferredWords),
        ]);

		// Text length limit: 1 million characters
		$len = mb_strlen(trim($text), 'UTF-8');
		if ($len <= 1) {
			throw new InvalidArgumentException('Text contains not enough characters.');
		}
		
		if ($len > 1000000) {
			throw new InvalidArgumentException('Text exceeds maximum allowed length of 1,000,000 characters.');
		}

		// Clean text before processing
		$this->text = $this->cleanText($text);
		$this->textLength = $len;

		if(!is_numeric($maxWords)) {
			throw new InvalidArgumentException('maxWords must be an integer.');
		}

		if ($maxWords < 20 || $maxWords > 150) {
			throw new InvalidArgumentException('maxWords must be between 20 and 150.');
		}
		$this->maxWords = $maxWords;

		if (count($forbiddenWords) > 10 ) {
			throw new InvalidArgumentException('forbiddenWords must each contain at most 10 items.');
		}

        if ( count($preferredWords) > 10) {
			throw new InvalidArgumentException('preferredWords must each contain at most 10 items.');
		}

		// Ensure inputs themselves don't contain duplicates handled by normalize
		$forbidden = $this->normalizeArray($forbiddenWords);
		$preferred = $this->normalizeArray($preferredWords);
		Log::debug("WordCounter: normalized word lists", [
			'forbidden_words' => json_encode($forbidden),
			'preferred_words' => json_encode($preferred),
		]);

		// Merge stopwords and forbidden into forbiddenWordList
		$mergedForbidden = array_unique(array_merge($this->dutchStopwords, $this->englishStopwords, $forbidden));

		// Remove any preferred words from forbidden list
		$this->forbiddenWordList = array_values(array_filter($mergedForbidden, function($word) use ($preferred) {
			return !in_array($word, $preferred, true);
		}));

		Log::debug("WordCounter: forbidden words", [
			'forbidden_words' => json_encode($this->forbiddenWordList),
		]);

		$this->preferredWordList = $preferred;

		$this->ready = true;

		Log::info("WordCounter: validation passed");
	}

	/**
	 * Run the counting process and return formatted results.
	 *
	 * @return array
	 */
	public function run(): array
	{
		Log::info("WordCounter: start", [
            'text_length' => strlen($this->text),
        ]);

		if (!$this->ready) {
			throw new InvalidArgumentException('WordCounter not ready. Call ValidateAndSetParameters first.');
		}

		$parts = $this->splitText();

		$partialResults = [];
		$forbiddenWordsBlocked = [];

		// Try to use parallel runtime if available, fallback to sequential
		if ($this->isParallelActive()) {
			Log::info("WordCounter mode: parrallel");

			$runtimes = [];
			$futures = [];

			foreach ($parts as $i => $part) {
				$runtime = new \parallel\Runtime();
				$runtimes[] = $runtime;
				// Use the worker class inside the closure to keep helpers centralized
				$futures[] = $runtime->run(function($textPart, $forbidden) {
					require __DIR__ . '/../../vendor/autoload.php'; // zorgt dat autoloading werkt
					return \App\Engine\WordCountPartWorker::processPart($textPart, $forbidden);
				}, [$part, $this->forbiddenWordList, $this->preferredWordList]);
			}

			// Collect futures
			foreach ($futures as $f) {
				$wordCountResults = $f->value();
				$partialResults[] = $wordCountResults['counts'];
				$forbiddenWordsBlocked = array_merge($forbiddenWordsBlocked, $wordCountResults['blocked']);
			}
		} else {
			// Sequential fallback — use the same worker logic to keep behaviour consistent
			Log::info("WordCounter mode: sequential");
			foreach ($parts as $part) {
				// $part is an array of words (splitText returns arrays)
				$wordCountResults = \App\Engine\WordCountPartWorker::processPart($part, $this->forbiddenWordList);
				$partialResults[] = $wordCountResults['counts'];
				$forbiddenWordsBlocked = array_merge($forbiddenWordsBlocked, $wordCountResults['blocked']);
			}
		}

		$this->forbiddenWordsBlocked = array_values(array_unique($this->forbiddenWordsBlocked));

		// Merge partial results
		$merged = $this->mergeParts($partialResults);
		
		// Final cleanup and formatting
		$formatted = $this->formatResults($merged);

		Log::info("WordCounter: finished", [
            'total_words' => $formatted['totalWords'],
			'results_words' => json_encode($formatted['cloudWords']),	
			'max_words' => $this->maxWords,
			'blocked_words' => json_encode($this->forbiddenWordsBlocked),
        ]);

		return $formatted;
	}

	/**
	 * Replace punctuation with spaces, keeping ' and - as part of words.
	 *
	 * @param string $textPart
	 * @return string
	 */
	private function cleanText(string $text): string
	{
		// replace unwanted characters with space
		$clean = preg_replace('/[^\p{L}\p{N}\'-]+/u', ' ', $text);
		// remove double spaces
		$clean = trim(preg_replace('/\s+/', ' ', $clean));
		return $clean === null ? '' : $clean;
	}

	/**
	 * Format results and limit to maxWords. Returns array as specified.
	 *
	 * @param array $results
	 * @return array {
	 *   totalWords: int,
	 *   cloudWords: array<int, array{word: string, count: int}>
	 * }
	 */
	private function formatResults(array $results): array
	{
		$filtered = $this->filterMaxResults($results);

		// Build cloudWords with int-based numeric keys
		$cloudWords = [];
		$i = 1;
		foreach ($filtered as $word => $count) {
			$cloudWords[$i] = ['word' => (string) $word, 'count' => $count];
			$i++;
		}

		return [
			'totalWords' => count($filtered),
			'cloudWords' => $cloudWords,
		];
	}

	/**
	 * Limit results to maxWords and ensure preferredWords are present first.
	 *
	 * @param array $results (assumed sorted desc)
	 * @return array
	 */
	private function filterMaxResults(array $results): array
	{
		// Descending by count
		arsort($results);

		$endResults = [];

		// Add preferred words first (if present in results)
		foreach ($this->preferredWordList as $req) {
			if (isset($results[$req])) {
				$endResults[$req] = $results[$req];
				unset($results[$req]);
			}
			if (count($endResults) >= $this->maxWords) break;
		}

		// Now add remaining results, up to maxWords
		$remaining = $this->maxWords - count($endResults);

		if ($remaining > 0) {
			foreach ($results as $word => $count) {
				$endResults[$word] = $count;
				$remaining--;
				if ($remaining === 0) break;
			}
		}

		return $endResults;
	}

	/**
	 * Merge partial results into array
	 * @param array<int, array<string, int>> $partialResults Array of partial word count arrays.
 	 * @return array<string, int> Merged word counts, with words as keys and counts as values.
	 */

	private function mergeParts(array $partialResults) :array
	{
		$merged = [];
		foreach ($partialResults as $pr) {
			foreach ($pr as $word => $cnt) {
				if (!isset($merged[$word])) $merged[$word] = 0;
				$merged[$word] += $cnt;
			}
		}

		return $merged;
	}

	/**
	 * Normalize input word lists: lowercase, but keep all-uppercase intact, trim, unique
	 * @param array<string> $arr
	 * @return array<string>
	 */

	private function normalizeArray(array $arr): array
	{
		$out = [];
		foreach ($arr as $word) {
			if (!is_string($word)) continue;
			if (trim($word) === '') continue;
			if (mb_strtoupper($word, 'UTF-8') === $word ) {
				// Keep all-uppercase words
				array_push($out, trim($word));
			} else {
				$word = mb_strtolower($word, 'UTF-8');
				array_push($out, trim($word));
			}
		}
		return $out;
	}

	/**
	 * Split the full text into 5 parts by words to avoid cutting words.
	 *
	 * @return array<array<string>>
	 */
	private function splitText(): array
	{
		$words = preg_split('/\s+/u', trim($this->text));
		if (!is_array($words) || count($words) === 0) {
			return array_fill(0, 5, '');
		}

		$total = count($words);
		$per = (int) ceil($total / 5);
		$parts = [];
		$i = 0;
		while ($i < $total) {
			$chunk = array_slice($words, $i, $per);
			$parts[] = $chunk;
			$i += $per;
		}

		return $parts;
	}
}

