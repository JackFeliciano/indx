# InDX

InDX stands for Interstitial Document Extended. It connects a person to their external accounts, documents, contact details, social profiles and financial information through a digital card reached by a short link.

The card stores fingerprints rather than original documents. Identity documents never travel with the card. Holders control published documents and grant named verifiers access to specific sections for defined periods. Each verifier sees only the information covered by their grant. Holders can review access history, including which verifier opened each section and when.

InDX uses plain PHP 8.2, requires no dependencies or database server, and runs on nginx with routing adapted from Apache rewrite rules.

## What the card holds

A card belongs to one holder. It carries his name, his date of birth and
optionally a photo. Beside that it holds three lists.

Documents are passports, licences, tax records, diplomas and the like. The card
never carries the document. It carries the kind, who issued it, when it expires
and a sha256 fingerprint of the contents, so a verifier can check the same
document against the issuer without the card becoming a copy of it.

Profiles are the accounts the holder keeps elsewhere, such as a repository or a
professional network. The card holds the address and the handle. It holds no
password and no token, so a leaked card opens no account.

A score belongs to a bureau and to a day. The card carries all three, the
bureau, the number and the date it was read, so no reader mistakes it for a
score the card computed. A score older than ninety days reports itself stale.

## Selective access

Every field other than the name and the photo comes with a grant. A grant names
one verifier, one field and one expiry date, and the holder can revoke it.

A verifier asks the registry and receives only the fields he was granted. A
field with no live grant does not appear in the answer at all, so a reader
cannot tell a field he was refused from a field the card never had. Two
verifiers asking about the same card receive two different answers, and the
page shows both side by side.

Revocation takes effect on the next question. The card is rebuilt with the grant
marked revoked, and the same verifier then reads nothing.

## Running it

    php tests/run.php      the suite, 37 checks, no dependencies
    php -S 127.0.0.1:8095 -t showroom showroom/index.php

Then open `http://127.0.0.1:8095/`. The prefix lives in INDX_BASE, which
defaults to `/`.

Routes: `/` the sample card, `/card/{handle}` one card, `/answer/{handle}/{verifier}`
what one verifier reads, `/health`.

## Layout

    src/Card/     Identity, Document, Profile, Grant, Score, Card, Issuer, Sample
    src/App/      Router, Views, Front
    showroom/     the front controller and the stylesheet
    tests/        the runner, the assertions and five test files
    deploy/       the systemd unit and the nginx location

## The sample

The sample card uses invented names and fingerprints of invented text. A card
that carried a real passport number would be a card carrying a real person, and
this repository has no reason to hold one.

## Licence

Mozilla Public License 2.0. See LICENSE.
