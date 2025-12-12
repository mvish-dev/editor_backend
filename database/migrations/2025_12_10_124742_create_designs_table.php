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
        Schema::create('designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->longText('canvas_data');
            $table->string('image_url')->nullable();
            $table->boolean('is_template')->default(false);
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft');
            $table->integer('cost')->default(0); // Token cost
            $table->string('sides')->default('Single'); // Single or Double
            $table->string('margin')->nullable(); // e.g., '3mm'
            $table->string('colors')->default('CMYK'); // e.g., 'CMYK', 'RGB'
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('designs');
    }
};
