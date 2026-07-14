<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('filament-tutorials.progress.table', 'filament_tutorial_progress'), function (Blueprint $table): void {
            $table->id();
            $table->string('user_type', 191);
            $table->string('user_id', 191);
            $table->string('panel_id', 64);
            $table->string('tutorial_key', 191);
            $table->string('status');
            $table->string('last_step_key')->nullable();
            $table->unsignedInteger('last_step_index')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('dismissed_at')->nullable();
            $table->timestamp('restarted_at')->nullable();
            $table->timestamps();

            $table->unique(['user_type', 'user_id', 'panel_id', 'tutorial_key'], 'filament_tutorial_progress_user_tutorial_unique');
            $table->index(['panel_id', 'tutorial_key']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('filament-tutorials.progress.table', 'filament_tutorial_progress'));
    }
};
