<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        $usesPostgres = DB::connection()->getDriverName() === 'pgsql';

        if ($usesPostgres) {
            Schema::ensureVectorExtensionExists();
        }

        Schema::create('project_knowledge_chunks', function (Blueprint $table) use ($usesPostgres) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_knowledge_source_id')->constrained()->cascadeOnDelete();
            $table->text('content');

            if ($usesPostgres) {
                $table->vector('embedding', dimensions: 1536);
            } else {
                $table->json('embedding');
            }

            $table->unsignedInteger('position');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('project_id');
            $table->unique(['project_knowledge_source_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_knowledge_chunks');
    }
};
