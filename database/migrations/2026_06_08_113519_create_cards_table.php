<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->string('code', 12)->unique();          // identifiant public /c/{code}
            $table->uuid('edit_token')->unique();           // édition sans compte

            $table->string('title')->nullable();            // nom affiché (ex: @alvine)
            $table->string('subtitle')->nullable();         // sous-titre (ex: Snapchat)
            $table->string('header_label')->nullable();
            $table->string('header_value')->nullable();

            $table->json('fields')->nullable();             // champs face, max 4 [{label, value}]
            $table->json('back_fields')->nullable();        // champs dos, illimités

            $table->string('image_path')->nullable();

            $table->string('bg_color', 7)->default('#DD7FF9');
            $table->string('text_color', 7)->default('#FFFFFF');

            $table->string('qr_type')->default('none');     // none|url|snapchat|instagram|linkedin|text
            $table->text('qr_value')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cards');
    }
};
