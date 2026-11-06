<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * Who the card belongs to. The name and the photo are the only two fields a
 * verifier may show back to the holder, because everything else on the card
 * comes with a grant attached.
 */
final class Identity
{
    public function __construct(
        public readonly string $holder,
        public readonly string $name,
        public readonly ?string $photoPath,
        public readonly string $bornOn,
    ) {
        if ($holder === '' || $name === '') {
            throw new \InvalidArgumentException('an identity names its holder');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $bornOn)) {
            throw new \InvalidArgumentException('a holder carries a date of birth');
        }
    }

    public function age(string $today): int
    {
        $born = new \DateTimeImmutable($this->bornOn);
        $now = new \DateTimeImmutable($today);

        return (int) $born->diff($now)->y;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['name' => $this->name, 'born_on' => $this->bornOn, 'photo' => $this->photoPath !== null];
    }
}
