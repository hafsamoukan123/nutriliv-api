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
            $table->foreignId('vendeur_id')->constrained('users')->onDelete('cascade');
    // البائع صاحب المنتج — إذا حُذف البائع تُحذف منتجاته
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);   // السعر بدقة خانتين عشريتين
            $table->integer('stock')->default(0); // الكمية المتوفرة
            $table->string('image')->nullable();  // مسار الصورة
            $table->boolean('is_active')->default(true); // إخفاء المنتج دون حذفه
            $table->timestamps();
            $table->integer('calories')->nullable();
            $table->integer('prep_time')->nullable(); // en minutes
            $table->string('allergens')->nullable();
            $table->boolean('is_featured')->default(false);
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
