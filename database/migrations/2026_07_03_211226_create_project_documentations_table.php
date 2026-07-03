<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('project_documentations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('media_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('type');
            $table->unsignedTinyInteger('category');
            $table->unsignedTinyInteger('visibility')->default(1);
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'type']);
            $table->index(['project_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_documentations');
    }
};
