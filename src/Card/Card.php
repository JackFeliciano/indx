<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * The card itself. It ties a person to everything they hold elsewhere and it
 * answers a verifier with the fields that verifier was granted. Nothing on it
 * carries a password, a token or a document, only proof that a document exists
 * and who may read it.
 */
final class Card
{
    /** @param list<Document> $documents @param list<Profile> $profiles @param list<Grant> $grants */
    public function __construct(
        public readonly Identity $identity,
        public readonly array $documents,
        public readonly array $profiles,
        public readonly array $grants,
        public readonly ?Score $score = null,
    ) {
    }

    /** The fields a named verifier may read today. */
    /** @return list<string> */
    public function fieldsFor(string $verifier, string $today): array
    {
        $fields = [];
        foreach ($this->grants as $grant) {
            if ($grant->verifier === $verifier && $grant->isLive($today)) {
                $fields[] = $grant->field;
            }
        }

        return array_values(array_unique($fields));
    }

    /**
     * What a verifier actually sees. A field with no live grant does not appear
     * at all, so a missing grant and a hidden field look the same from outside.
     *
     * @return array<string, mixed>
     */
    public function view(string $verifier, string $today): array
    {
        $out = ['verifier' => $verifier];
        foreach ($this->fieldsFor($verifier, $today) as $field) {
            $out[$field] = match ($field) {
                'name' => $this->identity->name,
                'photo' => $this->identity->photoPath,
                'documents' => array_map(
                    static fn (Document $d): array => $d->toArray(),
                    $this->documents,
                ),
                'profiles' => array_map(
                    static fn (Profile $p): array => $p->toArray(),
                    $this->profiles,
                ),
                'score' => $this->score?->toArray(),
                'address' => 'held by the issuer',
                default => null,
            };
        }

        return $out;
    }

    /** @return list<string> */
    public function expiredFieldsDocumented(string $today): array
    {
        $out = [];
        foreach ($this->documents as $document) {
            if ($document->isExpired($today)) {
                $out[] = $document->kind;
            }
        }

        return $out;
    }

    public function revoke(string $verifier, string $field): self
    {
        $grants = [];
        foreach ($this->grants as $grant) {
            $grants[] = ($grant->verifier === $verifier && $grant->field === $field)
                ? $grant->revoke()
                : $grant;
        }

        return new self($this->identity, $this->documents, $this->profiles, $grants, $this->score);
    }

    /** @return array<string, mixed> */
    public function toArray(string $today): array
    {
        return [
            'identity' => $this->identity->toArray(),
            'documents' => array_map(static fn (Document $d): array => $d->toArray(), $this->documents),
            'profiles' => array_map(static fn (Profile $p): array => $p->toArray(), $this->profiles),
            'score' => $this->score?->toArray(),
            'grants' => array_map(static fn (Grant $g): array => $g->toArray(), $this->grants),
            'expired' => $this->expiredFieldsDocumented($today),
        ];
    }
}
