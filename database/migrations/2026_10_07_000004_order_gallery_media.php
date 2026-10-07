<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('entry_media', fn (Blueprint $t) => $t->unsignedInteger('sort_order')->default(0));
    }

    public function down(): void
    {
        Schema::table('entry_media', fn (Blueprint $t) => $t->dropColumn('sort_order'));
    }
};
