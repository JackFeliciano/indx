<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * One permission, given to one verifier, on one field, with an expiry. The card
 * is the selector, and this is what a selector is for: a verifier reads the one
 * field he was granted and nothing else.
 */
final class Grant
{
    public const FIELDS = ['name', 'photo', 'documents', 'profiles', 'score', 'address'];

    public function __construct(
        public readonly string $verifier,
        public readonly string $field,
        public readonly string $expiresOn,
        public readonly bool $revoked = false,
    ) {
        if (!in_array($field, self::FIELDS, true)) {
            throw new \InvalidArgumentException('unknown field ' . $field);
        }
        if ($verifier === '') {
            throw new \InvalidArgumentException('a grant names its verifier');
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiresOn)) {
            throw new \InvalidArgumentException('a grant carries an expiry date');
        }
    }

    public function isLive(string $today): bool
    {
        return !$this->revoked && $this->expiresOn >= $today;
    }

    public function revoke(): self
    {
        return new self($this->verifier, $this->field, $this->expiresOn, true);
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'verifier' => $this->verifier,
            'field' => $this->field,
            'expires_on' => $this->expiresOn,
            'revoked' => $this->revoked,
        ];
    }
}
