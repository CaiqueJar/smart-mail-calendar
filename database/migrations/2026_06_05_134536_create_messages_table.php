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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId("user_id")
                ->constrained("users")
                ->cascadeOnDelete();
            $table->string("source_id");
            $table->string("title");
            $table->string("sender");
            $table->timestamp("sent_at");
            $table->enum("priority", ["Baixa", "Media", "Alta"]);
            $table->text("content");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
