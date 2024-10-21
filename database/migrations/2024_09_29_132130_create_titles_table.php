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
            $table->string('slug')->index()->nullable();
            $table->unsignedTinyInteger('type')->index();
            $table->unsignedTinyInteger('kind')->index();
            $table->string('title');
            $table->string('title_orig')->nullable();
            $table->string('other_title')->nullable();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration')->nullable();
            $table->unsignedTinyInteger('status')->index();
            $table->unsignedTinyInteger('state')->index()->nullable();
            $table->unsignedTinyInteger('minimal_age')->default(0);
            $table->unsignedSmallInteger('year')->nullable();
            $table->date('released_at')->nullable();
            $table->unsignedBigInteger('shikimori_id')->index();
            $table->unsignedSmallInteger('shikimori_rating')->nullable()->index();
            $table->unsignedBigInteger('shikimori_votes')->nullable();
            $table->unsignedSmallInteger('rating')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedSmallInteger('group_sort')->nullable();
            $table->json('blocked_countries')->nullable();
            $table->json('blocked_seasons')->nullable();
            $table->unsignedSmallInteger('last_episode')->nullable();
            $table->unsignedSmallInteger('episodes_count')->nullable();

            $table->timestamp('last_episode_at')->nullable();
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
