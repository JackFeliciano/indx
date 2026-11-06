<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * The registry that stands behind a card. A verifier asks the registry, and the
 * registry answers with one field at a time, so the card in the holder's hand
 * never holds more than a pointer.
 */
final class Issuer
{
    /** @var array<string, Card> */
    private array $cards = [];

    public function register(string $handle, Card $card): void
    {
        if (isset($this->cards[$handle])) {
            throw new \RuntimeException('a card already exists for ' . $handle);
        }
        $this->cards[$handle] = $card;
    }

    public function card(string $handle): Card
    {
        if (!isset($this->cards[$handle])) {
            throw new \RuntimeException('no card for ' . $handle);
        }

        return $this->cards[$handle];
    }

    public function has(string $handle): bool
    {
        return isset($this->cards[$handle]);
    }

    /** Everything a verifier may read, as one flat answer. */
    /** @return array<string, mixed> */
    public function answer(string $handle, string $verifier, string $today): array
    {
        return $this->card($handle)->view($verifier, $today);
    }

    public function count(): int
    {
        return count($this->cards);
    }
}
