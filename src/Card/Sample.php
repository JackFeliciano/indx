<?php

declare(strict_types=1);

namespace Indx\Card;

/**
 * A worked card, so the page has something to show. The names are invented and
 * the fingerprints are of invented text, because a card that carried a real one
 * would be a card carrying a real person.
 */
final class Sample
{
    public static function card(): Card
    {
        $documents = [
            new Document('passport', 'Ministry of Foreign Affairs', hash('sha256', 'sample passport'), '2031-04-18'),
            new Document('driver licence', 'State traffic authority', hash('sha256', 'sample licence'), '2029-09-02'),
            new Document('proof of address', 'Utility company', hash('sha256', 'sample utility bill'), '2026-12-01'),
        ];
        $profiles = [
            new Profile('github', 'sample-holder', 'https://github.com/sample-holder'),
            new Profile('linkedin', 'sample-holder', 'https://www.linkedin.com/in/sample-holder'),
            new Profile('personal site', 'sample-holder', 'https://example.org/sample-holder'),
        ];
        $grants = [
            new Grant('Borderless Bank', 'name', '2027-01-31'),
            new Grant('Borderless Bank', 'documents', '2027-01-31'),
            new Grant('Noma Property', 'score', '2026-11-30'),
            new Grant('Open Source Fund', 'profiles', '2026-12-31'),
            new Grant('Old Employer', 'documents', '2026-01-31'),
        ];

        return new Card(
            identity: new Identity('sample-holder', 'Sample Holder', 'assets/holder.jpg', '1984-03-27'),
            documents: $documents,
            profiles: $profiles,
            grants: $grants,
            score: new Score('Sample Bureau', 742, 1000, '2026-10-14'),
        );
    }

    public static function registry(): Issuer
    {
        $issuer = new Issuer();
        $issuer->register('sample-holder', self::card());

        return $issuer;
    }

    public static function today(): string
    {
        return '2026-11-06';
    }
}
