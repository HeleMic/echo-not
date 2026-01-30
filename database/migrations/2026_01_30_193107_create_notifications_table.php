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
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('application_id')
                ->constrained(table: 'applications', column: 'id')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->enum('type', ['email', 'sms', 'websocket']);
            $table->string('title');
            $table->text('html');
            $table->text('text');
            $table->json('recipient');
            $table->json('options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
