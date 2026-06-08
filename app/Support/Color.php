<?php

namespace App\Support;

class Color
{
    /**
     * Convertit un hex (#RRGGBB) en chaîne rgb(r, g, b) attendue par les passes Apple.
     */
    public static function hexToRgbString(string $hex): string
    {
        [$r, $g, $b] = self::hexToRgb($hex);

        return "rgb($r, $g, $b)";
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! ctype_xdigit($hex) || strlen($hex) !== 6) {
            return [221, 127, 249]; // lavande par défaut
        }

        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    public static function isValidHex(string $hex): bool
    {
        return (bool) preg_match('/^#[0-9A-Fa-f]{6}$/', trim($hex));
    }
}
