<?php

declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('project_documentations', function (Blueprint $table) {
            $table->string('file_original_name', 255)->nullable()->after('file_path');
        });
    }

    public function down(): void
    {
        Schema::table('project_documentations', function (Blueprint $table) {
            $table->dropColumn('file_original_name');
        });
    }
};
