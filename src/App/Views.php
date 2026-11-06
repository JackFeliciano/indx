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
        $out = $this->header('InDX Identity Card');
        $out .= '<h1>' . $this->h('InDX Identity Card') . '</h1>' . "\n";
        $out .= '<p class="lede">' . $this->h('InDX stands for Interstitial Document Extended. It gives each person a digital card that connects their identity to accounts, documents, contact details and social profiles elsewhere. A short link opens the card. The holder controls its contents, publishes selected documents for download and grants named verifiers access to specific information for a defined period.') . '</p>' . "\n";
        $out .= '<p class="holder">' . $this->h($card->identity->holder)
            . ' · born ' . $this->h($card->identity->bornOn)
            . ' · ' . $card->identity->age($today) . ' years old</p>' . "\n";

        $out .= '<h2>Six Parts of the Card</h2>' . "\n";
        $out .= '<p class="holder">' . $this->h('Every part below carries the sample holder\'s own values, so a reader sees what a verifier would read.') . '</p>' . "\n";
        $out .= '<div class="parts">' . "\n";
        foreach ($this->parts($card, $today) as [$partName, $partValue, $partNote]) {
            $out .= '<article class="part"><h3>' . $this->h($partName) . '</h3>'
                . '<p class="value">' . $partValue . '</p>'
                . '<p class="note">' . $this->h($partNote) . '</p></article>' . "\n";
        }
        $out .= '</div>' . "\n";
        $out .= '<div class="sections">' . "\n";
        $out .= $this->documentsBlock($card, $today);
        $out .= $this->profilesBlock($card);
        $out .= $this->scoreBlock($card, $today);
        $out .= $this->grantsBlock($card, $today);
        $out .= '</div>' . "\n";

        $out .= '<h2>What Each Verifier Sees</h2>' . "\n";
        $out .= '<p>' . $this->h('The holder grants each verifier access to named parts of the card for a defined period. Each verifier sees only the fields covered by their grant. The card presents a different view to each verifier. The holder can review which verifier opened each part and when.') . '</p>' . "\n";
        $out .= $this->viewsTable($card, $today);

        return $out . $this->footer();
    }

    /**
     * The six parts with the sample holder's own values. A part with no value on
     * the card says so, because a blank line would read as a fault.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function parts(Card $card, string $today): array
    {
        $identity = $card->identity;

        $photo = '<img class="portrait" src="assets/holder.jpg" alt="Verified photograph of ' . $this->h($identity->name) . '" width="480" height="600">';
        $photoValue = $identity->photoPath === null ? '<span class="missing">no photograph</span>' : $photo;
        $photoNote = 'Checked against the passport on 2026-04-18 by Borderless Bank.';

        $documents = [];
        foreach ($card->documents as $document) {
            $line = '<li><strong>' . $this->h($document->kind) . '</strong> '
                . $this->h($document->issuer)
                . ($document->expiresOn !== null ? ', valid to ' . $this->h($document->expiresOn) : '');
            if ($document->isExpired($today)) {
                $line .= ' <span class="expired">expired</span>';
            }
            $documents[] = $line . '</li>';
        }
        $documentsValue = '<ul class="tight">' . implode('', $documents) . '</ul>';
        $documentsNote = 'Three documents registered. The card stores a fingerprint of each, and the document stays with its issuer.';

        $contacts = '<ul class="tight">'
            . '<li><strong>email</strong> holder@example.org <span class="live">verified</span></li>'
            . '<li><strong>telephone</strong> +351 900 000 000 <span class="live">verified</span></li>'
            . '<li><strong>address</strong> Rua do Exemplo 12, Lisbon <span class="live">proof of address, 2026-10-02</span></li>'
            . '</ul>';
        $contactsNote = 'Each contact carries the check that cleared it, not the document behind the check.';

        $profiles = [];
        foreach ($card->profiles as $profile) {
            $profiles[] = '<li><strong>' . $this->h($profile->network) . '</strong> <a href="'
                . $this->h($profile->url) . '" rel="noopener">' . $this->h($profile->handle) . '</a></li>';
        }
        $profilesValue = '<ul class="tight">' . implode('', $profiles) . '</ul>';
        $profilesNote = 'The card holds no password and no token, so a leaked card opens no account.';

        $score = $card->score;
        $financeValue = $score === null
            ? '<span class="missing">no score on this card</span>'
            : '<p class="figure">' . $score->value . '<span class="scale"> of ' . $score->scale . '</span></p>'
                . '<p class="note">' . $this->h($score->bureau) . ' on ' . $this->h($score->asOf)
                . ', band ' . $this->h($score->band()) . ($score->isStale($today) ? ' <span class="stale">older than ninety days</span>' : '') . '</p>'
                . '<p class="note">IBAN PT50 0000 0000 0000 0000 0000 0, held with Borderless Bank, proof of funds 2026-10-20.</p>';
        $financeNote = 'The bureau and the date travel with the number, so nobody reads it as a score the card computed.';

        $live = 0;
        $total = count($card->grants);
        foreach ($card->grants as $grant) {
            if ($grant->isLive($today)) {
                $live++;
            }
        }
        $grantsValue = '<p class="figure">' . $live . '<span class="scale"> of ' . $total . ' live</span></p>'
            . '<ul class="tight">';
        foreach ($card->grants as $grant) {
            $state = $grant->revoked ? 'revoked' : ($grant->isLive($today) ? 'live' : 'expired');
            $grantsValue .= '<li><strong>' . $this->h($grant->verifier) . '</strong> reads '
                . $this->h($grant->field) . ' to ' . $this->h($grant->expiresOn)
                . ' <span class="' . $state . '">' . $state . '</span></li>';
        }
        $grantsValue .= '</ul>';
        $grantsNote = 'The holder revokes a grant at any time, and the part disappears from that verifier\'s view.';

        return [
            ['Verified photograph', $photoValue, $photoNote],
            ['Personal documents', $documentsValue, $documentsNote],
            ['Validated contacts', $contacts, $contactsNote],
            ['Social profiles', $profilesValue, $profilesNote],
            ['Financial information', $financeValue, $financeNote],
            ['Access grants', $grantsValue, $grantsNote],
        ];
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
        $out = '<section class="block"><h3>' . $this->h('Grant View') . '</h3>' . "\n";
        $out .= '<p>' . $this->h('Each grant shows the named verifier, accessible card sections and access period.') . '</p>' . "\n" . '<ul>' . "\n";
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
