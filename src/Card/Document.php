<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * One piece of proof. A document has a kind, who issued it, when it expires and
 * a fingerprint of its contents, so a verifier can check the same document
 * without the card ever carrying the document itself.
 */
final class Document
{
    public const KINDS = [
        'passport',
        'national identity',
        'driver licence',
        'tax record',
        'proof of address',
        'diploma',
        'certificate',
    ];

    public function __construct(
        public readonly string $kind,
        public readonly string $issuer,
        public readonly string $identifierHash,
        public readonly ?string $expiresOn,
    ) {
        if (!in_array($kind, self::KINDS, true)) {
            throw new \InvalidArgumentException('unknown document kind ' . $kind);
        }
        if ($issuer === '') {
            throw new \InvalidArgumentException('a document names who issued it');
        }
        if (!preg_match('/^[a-f0-9]{64}$/', $identifierHash)) {
            throw new \InvalidArgumentException('the identifier is a sha256 hex digest');
        }
    }

    public function isExpired(string $today): bool
    {
        return $this->expiresOn !== null && $this->expiresOn < $today;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'issuer' => $this->issuer,
            'expires_on' => $this->expiresOn,
        ];
    }
}
