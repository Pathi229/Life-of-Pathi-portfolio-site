<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->boolean('is_admin')->default(false));
        Schema::create('branches', function (Blueprint $t) {
            $t->id();
            $t->foreignId('parent_id')->nullable()->constrained('branches')->restrictOnDelete();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->string('activity')->default('active');
            $t->string('icon')->default('✧');
            $t->string('accent')->default('#3e6753');
            $t->integer('sort_order')->default(0);
            $t->boolean('archived')->default(false);
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('channels', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('introduction')->nullable();
            $t->string('accent')->default('#3e6753');
            $t->json('social_links')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('series', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('slug')->unique();
            $t->text('description')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('entries', function (Blueprint $t) {
            $t->id();
            $t->string('type');
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->longText('body')->nullable();
            $t->string('publication')->default('draft');
            $t->string('visibility')->default('private');
            $t->string('maturity')->default('seed');
            $t->foreignId('primary_branch_id')->nullable()->constrained('branches')->restrictOnDelete();
            $t->foreignId('project_id')->nullable()->constrained('entries')->restrictOnDelete();
            $t->foreignId('series_id')->nullable()->constrained('series')->restrictOnDelete();
            $t->integer('sequence')->default(0);
            $t->boolean('featured')->default(false);
            $t->boolean('portfolio')->default(false);
            $t->integer('sort_order')->default(0);
            $t->string('category')->nullable();
            $t->string('difficulty')->nullable();
            $t->string('technique')->nullable();
            $t->string('role')->nullable();
            $t->string('platform')->nullable();
            $t->string('video_url')->nullable();
            $t->foreignId('article_id')->nullable()->constrained('entries')->restrictOnDelete();
            $t->json('case_study')->nullable();
            $t->string('seo_title')->nullable();
            $t->text('seo_description')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamp('meaningful_updated_at')->nullable();
            $t->date('started_at')->nullable();
            $t->date('finished_at')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        foreach (['branch', 'channel'] as $name) {
            Schema::create($name.'_entry', function (Blueprint $t) use ($name) {
                $t->foreignId($name.'_id')->constrained($name === 'branch' ? 'branches' : 'channels')->restrictOnDelete();
                $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
                $t->primary([$name.'_id', 'entry_id']);
            });
        }
        Schema::create('branch_channel', function (Blueprint $t) {
            $t->foreignId('branch_id')->constrained()->restrictOnDelete();
            $t->foreignId('channel_id')->constrained()->cascadeOnDelete();
            $t->primary(['branch_id', 'channel_id']);
        });
        Schema::create('media', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('path');
            $t->string('mime')->nullable();
            $t->text('alt')->nullable();
            $t->text('caption')->nullable();
            $t->string('credit')->nullable();
            $t->timestamps();
            $t->softDeletes();
        });
        Schema::create('entry_media', function (Blueprint $t) {
            $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
            $t->foreignId('media_id')->constrained('media')->restrictOnDelete();
            $t->primary(['entry_id', 'media_id']);
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->id();
            $t->string('name')->default('Life of Pathi');
            $t->string('tagline')->default('Still growing.');
            $t->text('introduction')->nullable();
            $t->string('contact_email')->nullable();
            $t->text('availability')->nullable();
            $t->json('social_links')->nullable();
            $t->text('now_content')->nullable();
            $t->date('now_date')->nullable();
            $t->foreignId('cv_media_id')->nullable()->constrained('media')->restrictOnDelete();
            $t->foreignId('pathway_id')->nullable()->constrained('entries')->restrictOnDelete();
            $t->timestamps();
        });
        Schema::create('slug_redirects', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->foreignId('entry_id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        foreach (['slug_redirects', 'settings', 'entry_media', 'media', 'branch_channel', 'channel_entry', 'branch_entry', 'entries', 'series', 'channels', 'branches'] as $table) {
            Schema::dropIfExists($table);
        } Schema::table('users', fn (Blueprint $t) => $t->dropColumn('is_admin'));
    }
};
