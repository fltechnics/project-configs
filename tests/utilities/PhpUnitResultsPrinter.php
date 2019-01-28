<?php

namespace Tests\utilities;

use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestSuite;
use Codedungeon\PHPUnitPrettyResultPrinter\Printer;

class PhpUnitResultsPrinter extends Printer
{
    protected $suit_time = 0;

    public function endTest(Test $test, float $time): void
    {
        $this->suit_time += $time;
        parent::endTest($test, $time);
    }

    /**
     * A testsuite ended.
     *
     * @param TestSuite $suite
     */
    public function endTestSuite(TestSuite $suite): void
    {
        $time = \round($this->suit_time, 2);
        $color = 'fg-white, bg-green';

        if ($time > 2) {
            $color = 'fg-white, bg-yellow';
        }

        if ($time > 4) {
            $color = 'fg-white, bg-red';
        }

        $this->writeWithColor(
            $color,
            \sprintf(' %s seconds ', $time)
        );

        $this->suit_time = 0;
    }
}
