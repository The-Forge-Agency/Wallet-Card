<?php

use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (! function_exists('qr_svg')) {
    /**
     * Génère un QR code au format SVG inline.
     *
     * @param  array{0:int,1:int,2:int}  $color  Couleur RGB des modules
     */
    function qr_svg(string $payload, int $size = 180, array $color = [0, 0, 0]): string
    {
        return QrCode::format('svg')
            ->size($size)
            ->margin(0)
            ->errorCorrection('M')
            ->color($color[0], $color[1], $color[2])
            ->generate($payload)
            ->toHtml();
    }
}
