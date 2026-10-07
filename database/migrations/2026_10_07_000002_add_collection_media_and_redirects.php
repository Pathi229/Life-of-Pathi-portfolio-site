<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channels', function (Blueprint $t) {
            $t->foreignId('cover_media_id')->nullable()->constrained('media')->restrictOnDelete();
        });
        Schema::create('collection_redirects', function (Blueprint $t) {
            $t->id();
            $t->string('kind');
            $t->string('slug');
            $t->unsignedBigInteger('record_id');
            $t->unique(['kind', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collection_redirects');
        Schema::table('channels', fn (Blueprint $t) => $t->dropColumn('cover_media_id'));
    }
};
