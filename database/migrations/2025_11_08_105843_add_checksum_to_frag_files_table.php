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
        Schema::table('frag_files', function (Blueprint $table) {
            $table->string('checksum', 64)
                ->nullable()
                ->after('mime_type')
                ->comment('SHA-256 hash of file content');
            $table->unique(['user_id', 'checksum'], 'frag_files_user_checksum_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('frag_files', function (Blueprint $table) {
            $table->dropUnique('frag_files_user_checksum_unique');
            $table->dropColumn('checksum');
        });
    }
};
