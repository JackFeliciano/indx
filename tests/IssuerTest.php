<?php

declare(strict_types=1);

use Indx\Card\Grant;
use Indx\Card\Issuer;
use Indx\Card\Sample;
use Indx\Tests\Assert;

return [
    ['the registry holds one card', static function (): void {
        Assert::same(1, Sample::registry()->count());
    }],
    ['the registry answers a verifier', static function (): void {
        $answer = Sample::registry()->answer('sample-holder', 'Borderless Bank', Sample::today());
        Assert::same('Sample Holder', $answer['name']);
    }],
    ['asking for a card that does not exist raises', static function (): void {
        try {
            Sample::registry()->card('nobody');
            Assert::true(false, 'expected a rejection');
        } catch (\RuntimeException) {
            Assert::true(true);
        }
    }],
    ['a handle registers once', static function (): void {
        $issuer = new Issuer();
        $issuer->register('h', Sample::card());
        try {
            $issuer->register('h', Sample::card());
            Assert::true(false, 'expected a rejection');
        } catch (\RuntimeException) {
            Assert::true(true);
        }
        Assert::same(1, $issuer->count());
    }],
    ['an unknown handle answers with no fields', static function (): void {
        $answer = Sample::registry()->answer('sample-holder', 'Nobody', Sample::today());
        Assert::count(1, $answer);
    }],
];
