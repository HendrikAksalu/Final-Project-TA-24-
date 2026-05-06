<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('album_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title')->default('Mälestus');
            $table->text('story')->nullable();
            $table->text('who')->nullable();
            $table->string('when')->nullable();
            $table->text('where_note')->nullable();
            $table->longText('image_url')->nullable();
            $table->longText('image_thumb_url')->nullable();
            $table->string('photo_class')->default('one');
            $table->boolean('favorite')->default(false);
            $table->string('rotate')->default('');
            $table->json('face_markers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memories');
    }
};
