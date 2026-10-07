<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['entries', 'branches', 'channels', 'series'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->string('demo_key')->nullable()->unique());
        }
    }

    public function down(): void
    {
        foreach (['entries', 'branches', 'channels', 'series'] as $table) {
            Schema::table($table, fn (Blueprint $t) => $t->dropColumn('demo_key'));
        }
    }
};
