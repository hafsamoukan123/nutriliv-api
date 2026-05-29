<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Notification;
use Illuminate\Http\Request;

class LivreurController extends Controller
{
    // ✅ الطلبات المتاحة — مرتبة بحسب الأحدث
    public function availableOrders(Request $request)
    {
        $orders = Order::with(['client:id,name,phone', 'vendeur:id,name,shop_name,address'])
                       ->where('status', 'ready') // فقط الجاهزة
                       ->whereNull('livreur_id')  // لم يأخذها أحد بعد
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    // ✅ الساعي يقبل طلباً
    public function acceptOrder(Request $request, $id)
    {
        $order = Order::where('id', $id)
                      ->where('status', 'ready')
                      ->whereNull('livreur_id')
                      ->firstOrFail();

        $order->update([
            'livreur_id' => $request->user()->id,
            'status'     => 'picked_up',
        ]);

        // إشعار للزبون
        Notification::create([
            'user_id' => $order->client_id,
            'title'   => 'Livreur en route',
            'message' => "Un livreur a pris votre commande #{$order->id}",
            'type'    => 'order_status',
        ]);

        return response()->json(['message' => 'Commande acceptée']);
    }

    // ✅ تحديث حالة التوصيل
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:delivering,delivered'
        ]);

        $order = Order::where('id', $id)
                      ->where('livreur_id', $request->user()->id)
                      ->firstOrFail();

        $order->update(['status' => $request->status]);

        // ✅ عند التسليم — تأكيد الدفع وإضافة رصيد للبائع
        if ($request->status === 'delivered') {
            $order->update(['delivered_at' => now()]);

            // تأكيد الـ Payment
            $order->payment->update([
                'status'       => 'confirmed',
                'confirmed_at' => now(),
            ]);

            // إضافة صافي المبلغ لرصيد البائع
            $netAmount = $order->getVendeurAmount();
            $order->vendeur->increment('balance', $netAmount);

            // تسجيل Transaction
            Transaction::create([
                'vendeur_id'          => $order->vendeur_id,
                'order_id'            => $order->id,
                'amount'              => $order->total_amount,
                'commission_deducted' => $order->commission,
                'net_amount'          => $netAmount,
                'type'                => 'sale',
                'status'              => 'completed',
            ]);

            // إشعار للزبون والبائع
            Notification::create([
                'user_id' => $order->client_id,
                'title'   => 'Commande livrée',
                'message' => "Votre commande #{$order->id} a été livrée!",
                'type'    => 'order_status',
            ]);

            Notification::create([
                'user_id' => $order->vendeur_id,
                'title'   => 'Paiement reçu',
                'message' => "Paiement de {$netAmount} DH ajouté à votre solde",
                'type'    => 'payment_done',
            ]);
        }

        return response()->json(['message' => 'Statut mis à jour']);
    }

    // ✅ تحديث موقع الساعي — يُستدعى كل 10 ثوانٍ من React
    public function updateLocation(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $request->user()->update([
            'current_location' => "{$request->lat},{$request->lng}"
        ]);

        return response()->json(['message' => 'Position mise à jour']);
    }
}