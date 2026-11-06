<?php

declare(strict_types=1);

namespace Indx\App;

use Indx\Card\Card;
use Indx\Card\Grant;

/**
 * The HTML. Every value printed here passed a constructor that already refused
 * the dangerous shapes, and every string goes through h() anyway.
 */
final class Views
{
    public function __construct(private readonly string $base)
    {
    }

    private function h(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public function header(string $title): string
    {
        $base = $this->h($this->base);

        return <<<HTML
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{$this->h($title)}</title>
<base href="{$base}">
<link rel="stylesheet" href="assets/site.css">
</head>
<body>
<main>
HTML;
    }

    public function footer(): string
    {
        return "</main>\n</body>\n</html>\n";
    }

    public function card(Card $card, string $today): string
    {
        $out = $this->header('Identity card');
        $out .= '<h1>' . $this->h($card->identity->name) . '</h1>' . "\n";
        $out .= '<p class="holder">' . $this->h($card->identity->holder)
            . ' · born ' . $this->h($card->identity->bornOn)
            . ' · ' . $card->identity->age($today) . ' years old</p>' . "\n";

        $out .= '<h2>The card holds</h2>' . "\n";
        $out .= '<div class="sections">' . "\n";
        $out .= $this->documentsBlock($card, $today);
        $out .= $this->profilesBlock($card);
        $out .= $this->scoreBlock($card, $today);
        $out .= $this->grantsBlock($card, $today);
        $out .= '</div>' . "\n";

        $out .= '<h2>What each verifier sees</h2>' . "\n" . $this->viewsTable($card, $today);

        return $out . $this->footer();
    }

    private function documentsBlock(Card $card, string $today): string
    {
        $out = '<section class="block"><h3>Documents</h3><ul>' . "\n";
        foreach ($card->documents as $document) {
            $stale = $document->isExpired($today) ? ' <span class="stale">expired</span>' : '';
            $out .= '<li><strong>' . $this->h($document->kind) . '</strong>'
                . ' from ' . $this->h($document->issuer)
                . ($document->expiresOn !== null ? ', valid to ' . $this->h($document->expiresOn) : '')
                . $stale
                . '<span class="fingerprint">' . $this->h(substr($document->identifierHash, 0, 16)) . '…</span>'
                . '</li>' . "\n";
        }

        return $out . '</ul><p class="note">The card stores a fingerprint of each document. The document itself stays with whoever issued it.</p></section>' . "\n";
    }

    private function profilesBlock(Card $card): string
    {
        $out = '<section class="block"><h3>Accounts elsewhere</h3><ul>' . "\n";
        foreach ($card->profiles as $profile) {
            $out .= '<li><strong>' . $this->h($profile->network) . '</strong> '
                . '<a href="' . $this->h($profile->url) . '" rel="noopener">'
                . $this->h($profile->handle) . '</a></li>' . "\n";
        }

        return $out . '</ul><p class="note">No password and no token reaches the card, so a leaked card opens no account.</p></section>' . "\n";
    }

    private function scoreBlock(Card $card, string $today): string
    {
        if ($card->score === null) {
            return '<section class="block"><h3>Credit</h3><p class="note">No score on this card.</p></section>' . "\n";
        }

        $score = $card->score;
        $stale = $score->isStale($today) ? ' <span class="stale">older than ninety days</span>' : '';
        $out = '<section class="block"><h3>Credit</h3>' . "\n";
        $out .= '<p><strong>' . $score->value . '</strong> of ' . $score->scale
            . ' from ' . $this->h($score->bureau)
            . ' on ' . $this->h($score->asOf)
            . ', band ' . $this->h($score->band()) . $stale . '</p>' . "\n";

        return $out . '<p class="note">The card carries the bureau and the date with the number, so nobody mistakes it for a score the card computed.</p></section>' . "\n";
    }

    private function grantsBlock(Card $card, string $today): string
    {
        $out = '<section class="block"><h3>Access grants</h3><ul>' . "\n";
        foreach ($card->grants as $grant) {
            $live = $grant->isLive($today);
            $state = $grant->revoked ? 'revoked' : ($live ? 'live' : 'expired');
            $out .= '<li>' . $this->h($grant->verifier) . ' reads '
                . $this->h($grant->field) . ' to ' . $this->h($grant->expiresOn)
                . ' <span class="' . $state . '">' . $state . '</span></li>' . "\n";
        }

        return $out . '</ul></section>' . "\n";
    }

    private function viewsTable(Card $card, string $today): string
    {
        $verifiers = [];
        foreach ($card->grants as $grant) {
            $verifiers[$grant->verifier] = true;
        }

        $out = '<table><thead><tr><th>Verifier</th><th>Fields</th></tr></thead><tbody>' . "\n";
        foreach (array_keys($verifiers) as $verifier) {
            $fields = $card->fieldsFor((string) $verifier, $today);
            $out .= '<tr><td>' . $this->h((string) $verifier) . '</td><td>'
                . ($fields === [] ? '<span class="stale">nothing</span>' : $this->h(implode(', ', $fields)))
                . '</td></tr>' . "\n";
        }

        return $out . '</tbody></table>' . "\n";
    }

    public function answer(array $view): string
    {
        return $this->header('Verifier answer')
            . '<h1>' . $this->h((string) ($view['verifier'] ?? '')) . '</h1>'
            . '<pre>' . $this->h(json_encode($view, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: '') . '</pre>'
            . $this->footer();
    }

    public function notFound(string $path): string
    {
        return $this->header('Not found') . '<h1>Not found</h1><p>' . $this->h($path) . '</p>' . $this->footer();
    }
}
