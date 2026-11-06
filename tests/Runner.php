<?php

declare(strict_types=1);

namespace Indx\Tests;

/**
 * The whole test framework. No PHPUnit, no dependencies: a test file returns a
 * list of name and closure pairs and the runner counts what passed.
 */
final class Runner
{
    private int $passed = 0;
    private int $failed = 0;
    private int $checks = 0;

    /** @var list<string> */
    private array $failures = [];

    public function check(string $name, \Closure $test): void
    {
        $this->checks++;
        try {
            $test();
            $this->passed++;
        } catch (\Throwable $e) {
            $this->failed++;
            $this->failures[] = $name . ' :: ' . $e->getMessage();
        }
    }

    public function summary(): int
    {
        foreach ($this->failures as $failure) {
            fwrite(STDERR, 'FAIL ' . $failure . PHP_EOL);
        }
        fwrite(STDOUT, sprintf(
            '%d passed, %d failed, %d checks%s',
            $this->passed,
            $this->failed,
            $this->checks,
            PHP_EOL,
        ));

        return $this->failed === 0 ? 0 : 1;
    }
}
