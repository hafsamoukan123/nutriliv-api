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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vendeur_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('livreur_id')->nullable()->constrained('users')->onDelete('set null');
             // الساعي nullable لأنه يُعيَّن بعد الطلب

            $table->enum('status', [
               'pending',      // في انتظار قبول البائع
               'confirmed',    // البائع قبل الطلب
               'preparing',    // البائع يحضّر الطلب
               'ready',        // جاهز للاستلام من الساعي
               'picked_up',    // الساعي استلم الطلب
               'delivering',   // في الطريق
               'delivered',    // تم التوصيل والدفع
               'cancelled'     // ملغى
            ])->default('pending');

            $table->string('delivery_address');
            $table->decimal('total_amount', 10, 2);   // مجموع المنتجات
            $table->decimal('delivery_fee', 10, 2)->default(0); // رسوم التوصيل
            $table->decimal('commission', 10, 2)->default(0);   // عمولة الموقع
            $table->text('notes')->nullable();          // ملاحظات الزبون
            $table->timestamp('delivered_at')->nullable(); // وقت التسليم الفعلي
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
