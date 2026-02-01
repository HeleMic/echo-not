<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')
                ->constrained(table: 'applications', column: 'id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name')->unique();
            $table->string('key')->unique();
            $table->dateTime('last_used_at')
                ->nullable(true)
                ->default(null);
            $table->dateTime('expires_at');
            $table->dateTime('revoked_at')
                ->nullable(true)
                ->default(null);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
