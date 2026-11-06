<?php

declare(strict_types=1);

use Indx\Card\Card;
use Indx\Card\Document;
use Indx\Card\Grant;
use Indx\Card\Identity;
use Indx\Card\Profile;
use Indx\Card\Sample;
use Indx\Card\Score;
use Indx\Tests\Assert;

function today(): string
{
    return '2026-11-06';
}

return [
    ['a verifier reads the name he was granted', static function (): void {
        $view = Sample::card()->view('Borderless Bank', today());
        Assert::same('Sample Holder', $view['name']);
    }],
    ['a verifier never reads a field he was not granted', static function (): void {
        $view = Sample::card()->view('Borderless Bank', today());
        Assert::false(isset($view['score']), 'the bank was never granted the score');
        Assert::false(isset($view['profiles']), 'the bank was never granted the profiles');
    }],
    ['an expired grant gives nothing', static function (): void {
        $fields = Sample::card()->fieldsFor('Old Employer', today());
        Assert::count(0, $fields);
    }],
    ['a revoked grant stops giving at once', static function (): void {
        $card = Sample::card()->revoke('Noma Property', 'score');
        Assert::count(0, $card->fieldsFor('Noma Property', today()));
        Assert::same([], $card->fieldsFor('Noma Property', today()));
    }],
    ['two verifiers get two different answers', static function (): void {
        $card = Sample::card();
        $bank = $card->view('Borderless Bank', today());
        $fund = $card->view('Open Source Fund', today());
        Assert::true(isset($bank['documents']));
        Assert::false(isset($bank['profiles']));
        Assert::true(isset($fund['profiles']));
        Assert::false(isset($fund['documents']));
    }],
    ['a field with no grant does not appear at all', static function (): void {
        $view = Sample::card()->view('Nobody At All', today());
        Assert::count(1, $view);
        Assert::same('Nobody At All', $view['verifier']);
    }],
    ['an expired document is named', static function (): void {
        $expired = Sample::card()->expiredFieldsDocumented('2032-01-01');
        Assert::true(in_array('passport', $expired, true));
    }],
    ['a score names its bureau and the day it was read', static function (): void {
        $score = Sample::card()->score;
        Assert::same('Sample Bureau', $score->bureau);
        Assert::same('fair', $score->band());
        Assert::false($score->isStale(today()));
    }],
    ['a score older than ninety days is stale', static function (): void {
        Assert::true(Sample::card()->score->isStale('2027-06-01'));
    }],
    ['an identity reports an age', static function (): void {
        $identity = new Identity('holder', 'Holder', null, '1984-03-27');
        Assert::same(42, $identity->age('2026-11-06'));
    }],
    ['a card refuses a score above its scale', static function (): void {
        try {
            new Score('Bureau', 1001, 1000, '2026-01-01');
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
];
