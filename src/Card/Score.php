<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * A credit score belongs to a bureau and to a date. The card carries both, so a
 * reader knows whose number he is looking at and how old it is, and the card
 * never pretends to compute a score of its own.
 */
final class Score
{
    public function __construct(
        public readonly string $bureau,
        public readonly int $value,
        public readonly int $scale,
        public readonly string $asOf,
    ) {
        if ($bureau === '') {
            throw new \InvalidArgumentException('a score names its bureau');
        }
        if ($scale < 1 || $value < 0 || $value > $scale) {
            throw new \InvalidArgumentException('the value has to fit the scale');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $asOf)) {
            throw new \InvalidArgumentException('a score carries the day it was read');
        }
    }

    public function band(): string
    {
        $share = $this->value / $this->scale;
        if ($share >= 0.8) {
            return 'strong';
        }
        if ($share >= 0.6) {
            return 'fair';
        }

        return 'thin';
    }

    public function isStale(string $today, int $days = 90): bool
    {
        $read = strtotime($this->asOf);
        $now = strtotime($today);
        if ($read === false || $now === false) {
            return true;
        }

        return ($now - $read) / 86400 > $days;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'bureau' => $this->bureau,
            'value' => $this->value,
            'scale' => $this->scale,
            'band' => $this->band(),
            'as_of' => $this->asOf,
        ];
    }
}
