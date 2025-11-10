<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('frag_links', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->index()->unique();
            $table->enum('state', [
                'active',
                'revoked',
            ])->index();
            $table->dateTime('expires_at')->index()->nullable();
            $table->string('password_hash')->nullable();

            $table->foreignId('frag_file_id')->constrained('frag_files');
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->index(['frag_file_id', 'user_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('frag_links');
    }
};
