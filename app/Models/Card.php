<?php

namespace App\Models;

use App\Enums\QrType;
use Database\Factories\CardFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Card extends Model
{
    /** @use HasFactory<CardFactory> */
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'fields' => 'array',
            'back_fields' => 'array',
            'qr_type' => QrType::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Card $card): void {
            $card->code ??= self::generateUniqueCode();
            $card->edit_token ??= (string) Str::uuid();
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = Str::lower(Str::random(8));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /**
     * URL (ou texte) réellement encodé dans le QR code, ou null si aucun.
     */
    public function qrPayload(): ?string
    {
        return $this->qr_type->resolve($this->qr_value);
    }

    public function hasQr(): bool
    {
        return $this->qrPayload() !== null;
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function frontFields(): array
    {
        return $this->cleanFields($this->fields, 4);
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function backFields(): array
    {
        return $this->cleanFields($this->back_fields, null);
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $raw
     * @return list<array{label: string, value: string}>
     */
    private function cleanFields(?array $raw, ?int $limit): array
    {
        $fields = collect($raw ?? [])
            ->map(fn ($f) => [
                'label' => trim((string) ($f['label'] ?? '')),
                'value' => trim((string) ($f['value'] ?? '')),
            ])
            ->filter(fn ($f) => $f['label'] !== '' || $f['value'] !== '')
            ->values();

        if ($limit !== null) {
            $fields = $fields->take($limit);
        }

        return $fields->all();
    }

    public function displayTitle(): string
    {
        return $this->title ?: 'Ma carte';
    }
}
