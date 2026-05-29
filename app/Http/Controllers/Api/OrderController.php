<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    // ✅ إنشاء طلب جديد من السلة
    public function store(Request $request)
    {
        $request->validate([
            'delivery_address' => 'required|string',
            'notes'            => 'nullable|string',
        ]);

        $cart = Cart::with('items.product')->where('client_id', $request->user()->id)->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Panier vide'], 400);
        }

        // ✅ كل منتجات السلة من نفس البائع؟
        $vendeurIds = $cart->items->pluck('product.vendeur_id')->unique();
        if ($vendeurIds->count() > 1) {
            return response()->json([
                'message' => 'Commande d\'un seul vendeur à la fois'
            ], 400);
        }

        // ✅ نستخدم DB::transaction لضمان أن كل شيء يتم أو لا شيء
        $order = DB::transaction(function () use ($request, $cart, $vendeurIds) {
            $total       = $cart->getTotal();
            $deliveryFee = 20; // رسوم التوصيل ثابتة
            $commission  = $total * 0.05; // عمولة 5%

            $order = Order::create([
                'client_id'        => $request->user()->id,
                'vendeur_id'       => $vendeurIds->first(),
                'status'           => 'pending',
                'delivery_address' => $request->delivery_address,
                'notes'            => $request->notes,
                'total_amount'     => $total,
                'delivery_fee'     => $deliveryFee,
                'commission'       => $commission,
            ]);

            // ✅ ننسخ كل عناصر السلة إلى order_items
            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $item->product_id,
                    'quantity'   => $item->quantity,
                    'unit_price' => $item->unit_price,
                ]);

                // ✅ نخصم من المخزون
                $item->product->decrement('stock', $item->quantity);
            }

            // ✅ إنشاء Payment بحالة pending
            Payment::create([
                'order_id' => $order->id,
                'amount'   => $total + $deliveryFee,
                'method'   => 'COD',
                'status'   => 'pending',
            ]);

            // ✅ إشعار للبائع
            Notification::create([
                'user_id' => $vendeurIds->first(),
                'title'   => 'Nouvelle commande',
                'message' => "Vous avez une nouvelle commande #{$order->id}",
                'type'    => 'order_new',
            ]);

            // ✅ تفريغ السلة
            $cart->clear();

            return $order;
        });

        return response()->json($order->load('items.product'), 201);
    }

    // ✅ طلبات الزبون
    public function myOrders(Request $request)
    {
        $orders = Order::with(['items.product', 'payment', 'livreur:id,name,current_location'])
                       ->where('client_id', $request->user()->id)
                       ->latest()
                       ->paginate(10);

        return response()->json($orders);
    }

    // ✅ تتبع طلب — يرجع موقع الساعي
    public function track($id)
    {
        $order = Order::with('livreur:id,name,phone,current_location')
                      ->findOrFail($id);

        return response()->json([
            'status'  => $order->status,
            'livreur' => $order->livreur,
        ]);
    }

    // ✅ طلبات البائع
    public function vendeurOrders(Request $request)
    {
        $orders = Order::with(['items.product', 'client:id,name,phone'])
                       ->where('vendeur_id', $request->user()->id)
                       ->latest()
                       ->paginate(10);

        return response()->json($orders);
    }

    // ✅ البائع يغير حالة الطلب
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:confirmed,preparing,ready,cancelled'
        ]);

        $order = Order::where('id', $id)
                      ->where('vendeur_id', $request->user()->id)
                      ->firstOrFail();

        $order->update(['status' => $request->status]);

        // ✅ إشعار للزبون
        Notification::create([
            'user_id' => $order->client_id,
            'title'   => 'Statut de commande',
            'message' => "Votre commande #{$order->id} est maintenant: {$request->status}",
            'type'    => 'order_status',
        ]);

        return response()->json(['message' => 'Statut mis à jour']);
    }

    // ✅ لوحة مبيعات البائع
    public function dashboard(Request $request)
    {
        $vendeurId = $request->user()->id;

        return response()->json([
            'total_orders'    => Order::where('vendeur_id', $vendeurId)->count(),
            'total_revenue'   => Order::where('vendeur_id', $vendeurId)
                                      ->where('status', 'delivered')
                                      ->sum('total_amount'),
            'pending_orders'  => Order::where('vendeur_id', $vendeurId)
                                      ->where('status', 'pending')->count(),
            'balance'         => $request->user()->balance,
        ]);
    }

    // ✅ طلب تحويل رصيد
    public function requestTransfer(Request $request)
    {
        $request->validate(['amount' => 'required|numeric|min:1']);

        $user = $request->user();

        if ($user->balance < $request->amount) {
            return response()->json(['message' => 'Solde insuffisant'], 400);
        }

        Transaction::create([
            'vendeur_id'          => $user->id,
            'amount'              => $request->amount,
            'commission_deducted' => 0,
            'net_amount'          => $request->amount,
            'type'                => 'transfer',
            'status'              => 'pending',
        ]);

        return response()->json(['message' => 'Demande de virement envoyée']);
    }

    public function transactions(Request $request)
    {
        return response()->json(
            Transaction::where('vendeur_id', $request->user()->id)->latest()->paginate(10)
        );
    }
}