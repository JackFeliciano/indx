<?php

declare(strict_types=1);

use Indx\Card\Document;
use Indx\Tests\Assert;

$hash = hash('sha256', 'a document');

return [
    ['a document keeps its kind and its issuer', static function () use ($hash): void {
        $document = new Document('passport', 'Ministry', $hash, '2031-01-01');
        Assert::same('passport', $document->kind);
        Assert::same('Ministry', $document->issuer);
    }],
    ['an unknown kind is refused', static function () use ($hash): void {
        try {
            new Document('shopping list', 'Somebody', $hash, null);
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['an identifier has to be a sha256 digest', static function (): void {
        try {
            new Document('passport', 'Ministry', 'abc', null);
            Assert::true(false, 'expected a rejection');
        } catch (\InvalidArgumentException) {
            Assert::true(true);
        }
    }],
    ['an expiry is read against a day', static function () use ($hash): void {
        $document = new Document('passport', 'Ministry', $hash, '2031-01-01');
        Assert::false($document->isExpired('2026-11-06'));
        Assert::true($document->isExpired('2031-01-02'));
    }],
    ['a document with no expiry never expires', static function () use ($hash): void {
        $document = new Document('diploma', 'University', $hash, null);
        Assert::false($document->isExpired('2099-01-01'));
    }],
];
