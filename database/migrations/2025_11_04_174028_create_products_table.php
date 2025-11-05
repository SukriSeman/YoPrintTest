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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('unique_key', 100)->unique('products_unique_key');
            $table->string('product_title', 100);
            $table->text('product_description')->nullable();
            $table->string('style_no', 20)->nullable();
            $table->string('sanmar_mainframe_color', 50)->nullable();
            $table->string('size', 10)->nullable();
            $table->string('color_name', 50)->nullable();
            $table->decimal('piece_price', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
