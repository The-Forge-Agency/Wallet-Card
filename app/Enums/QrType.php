<?php

namespace App\Enums;

enum QrType: string
{
    case None = 'none';
    case Url = 'url';
    case Snapchat = 'snapchat';
    case Instagram = 'instagram';
    case Linkedin = 'linkedin';
    case Text = 'text';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Aucun QR',
            self::Url => 'Site web',
            self::Snapchat => 'Snapchat',
            self::Instagram => 'Instagram',
            self::Linkedin => 'LinkedIn',
            self::Text => 'Texte libre',
        };
    }

    /**
     * Ce que l'utilisateur tape (ex: pseudo, URL, texte).
     */
    public function placeholder(): string
    {
        return match ($this) {
            self::None => '',
            self::Url => 'https://mon-site.com',
            self::Snapchat => 'ton.pseudo',
            self::Instagram => 'ton.pseudo',
            self::Linkedin => 'ton-profil',
            self::Text => 'Le texte à encoder',
        };
    }

    /**
     * Transforme la valeur saisie en contenu réellement encodé dans le QR.
     */
    public function resolve(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($this === self::None || $value === '') {
            return null;
        }

        $handle = ltrim($value, '@');

        return match ($this) {
            self::Url => str_starts_with($value, 'http') ? $value : 'https://'.$value,
            self::Snapchat => 'https://www.snapchat.com/add/'.$handle,
            self::Instagram => 'https://www.instagram.com/'.$handle,
            self::Linkedin => str_contains($value, 'linkedin.com')
                ? (str_starts_with($value, 'http') ? $value : 'https://'.$value)
                : 'https://www.linkedin.com/in/'.$handle,
            self::Text => $value,
            self::None => null,
        };
    }
}
