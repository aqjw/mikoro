<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('titles', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->index();
            $table->unsignedTinyInteger('type')->index();
            $table->string('title');
            $table->string('title_orig')->nullable();
            $table->string('other_title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('duration')->nullable();
            $table->unsignedTinyInteger('status')->index();
            $table->unsignedTinyInteger('minimal_age')->default(0);
            $table->unsignedTinyInteger('year')->nullable();
            $table->unsignedBigInteger('shikimori_id')->index();
            $table->unsignedBigInteger('shikimori_rating')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->json('blocked_countries')->nullable();
            $table->json('blocked_seasons')->nullable();
            $table->unsignedTinyInteger('last_episode')->nullable();
            $table->unsignedTinyInteger('episodes_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titles');
    }
};
