<?php

declare(strict_types=1);

use Indx\Card\Grant;
use Indx\Tests\Assert;

return [
    ['a live grant is live on its last day', static function (): void {
        $grant = new Grant('A Bank', 'name', '2026-11-06');
        Assert::true($grant->isLive('2026-11-06'));
        Assert::false($grant->isLive('2026-11-07'));
    }],
    ['a revoked grant is never live', static function (): void {
        $grant = new Grant('A Bank', 'name', '2030-01-01');
        Assert::false($grant->revoke()->isLive('2026-11-06'));
    }],
    ['a grant refuses a field nobody defined', static function (): void {
        try {
            new Grant('A Bank', 'salary history', '2030-01-01');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a grant refuses an expiry that is not a day', static function (): void {
        try {
            new Grant('A Bank', 'name', 'next year');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a grant names its verifier', static function (): void {
        try {
            new Grant('', 'name', '2030-01-01');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
];
