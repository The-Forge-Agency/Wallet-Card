<?php

use App\Enums\QrType;

it('résout les pseudos sociaux en URL', function (string $type, string $value, ?string $expected) {
    expect(QrType::from($type)->resolve($value))->toBe($expected);
})->with([
    ['snapchat', 'alvine', 'https://www.snapchat.com/add/alvine'],
    ['snapchat', '@alvine', 'https://www.snapchat.com/add/alvine'],
    ['instagram', 'alvine', 'https://www.instagram.com/alvine'],
    ['linkedin', 'alvine', 'https://www.linkedin.com/in/alvine'],
    ['url', 'mon-site.com', 'https://mon-site.com'],
    ['url', 'https://deja.com', 'https://deja.com'],
    ['text', 'Bonjour', 'Bonjour'],
    ['none', 'peu importe', null],
    ['snapchat', '', null],
]);
