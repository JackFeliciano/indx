<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * An account somewhere else. The card holds the address and the handle, and it
 * holds no password and no token, so a leaked card cannot be used to enter an
 * account.
 */
final class Profile
{
    public const NETWORKS = ['github', 'linkedin', 'x', 'instagram', 'mastodon', 'personal site'];

    public function __construct(
        public readonly string $network,
        public readonly string $handle,
        public readonly string $url,
    ) {
        if (!in_array($network, self::NETWORKS, true)) {
            throw new \InvalidArgumentException('unknown network ' . $network);
        }
        if ($handle === '') {
            throw new \InvalidArgumentException('a profile names its handle');
        }
        if (!str_starts_with($url, 'https://') || str_contains($url, ' ')) {
            throw new \InvalidArgumentException('a profile address is an https address');
        }
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return ['network' => $this->network, 'handle' => $this->handle, 'url' => $this->url];
    }
}
