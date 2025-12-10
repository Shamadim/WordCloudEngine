<?php

namespace App\Tests\Unit;

use App\Engine\WordCounter;
use PHPUnit\Framework\TestCase;

class ParallelAvailabilityTest extends TestCase
{
    public function test_TC14_ParallelAvailableReturnsFalseWhenClassMissing(): void
    {
        // TC14
        $mock = $this->getMockBuilder(WordCounter::class)
                     ->onlyMethods(['parallelClassExists', 'isParallelAvailable', 'isParallelActive',])
                     ->getMock();

        $mock->method('parallelClassExists')->willReturn(false);

        $mock->isParallelAvailable();

        $this->assertFalse($mock->isParallelActive());
    }

    public function test_TC13_ParallelAvailableReturnsTrueWhenMockClassExists(): void
    {
        // TC13
        // Fake class exists
        if (!class_exists('\parallel\Runtime')) {
            eval('namespace parallel; class Runtime {}');
        }

        $wc = new WordCounter();
        $wc->isParallelAvailable();
        $this->assertTrue($wc->isParallelActive());
    }
}
