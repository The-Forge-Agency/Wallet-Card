<?php

namespace Database\Factories;

use App\Enums\QrType;
use App\Models\Card;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Card>
 */
class CardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $handle = $this->faker->userName();

        return [
            'code' => Str::lower(Str::random(8)),
            'edit_token' => (string) Str::uuid(),
            'title' => '@'.$handle,
            'subtitle' => $this->faker->randomElement(['Snapchat', 'Instagram', 'LinkedIn', 'Mon site']),
            'header_label' => null,
            'header_value' => null,
            'fields' => [
                ['label' => 'Lien', 'value' => $handle],
            ],
            'back_fields' => [
                ['label' => 'À propos', 'value' => $this->faker->sentence()],
            ],
            'bg_color' => $this->faker->randomElement(['#DD7FF9', '#3B82F6', '#10B981', '#F59E0B', '#EF4444']),
            'text_color' => '#FFFFFF',
            'qr_color' => '#000000',
            'qr_type' => QrType::Snapchat,
            'qr_value' => $handle,
        ];
    }

    public function withoutQr(): static
    {
        return $this->state(fn () => ['qr_type' => QrType::None, 'qr_value' => null]);
    }
}
