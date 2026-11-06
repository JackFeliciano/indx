<?php

declare(strict_types=1);

use Indx\Card\Profile;
use Indx\Tests\Assert;

return [
    ['a profile carries a network and a handle', static function (): void {
        $profile = new Profile('github', 'someone', 'https://github.com/someone');
        Assert::same('github', $profile->network);
        Assert::same('someone', $profile->handle);
    }],
    ['a profile address has to be https', static function (): void {
        try {
            new Profile('github', 'someone', 'http://github.com/someone');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a profile address cannot carry a space', static function (): void {
        try {
            new Profile('github', 'someone', 'https://github.com/some one');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['an unknown network is refused', static function (): void {
        try {
            new Profile('myspace', 'someone', 'https://myspace.com/someone');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['a profile with no handle is refused', static function (): void {
        try {
            new Profile('github', '', 'https://github.com/x');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
];
