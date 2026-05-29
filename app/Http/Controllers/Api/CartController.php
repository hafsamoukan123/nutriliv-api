<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // ✅ عرض السلة الحالية
    public function index(Request $request)
    {
        $cart = Cart::with(['items.product.category'])
                    ->where('client_id', $request->user()->id)
                    ->first();

        return response()->json([
            'cart'  => $cart,
            'total' => $cart ? $cart->getTotal() : 0,
        ]);
    }

    // ✅ إضافة منتج للسلة
    public function addItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // تحقق من توفر المخزون
        if (!$product->isAvailable()) {
            return response()->json(['message' => 'Produit non disponible'], 400);
        }
        if ($product->stock < $request->quantity) {
            return response()->json(['message' => 'Stock insuffisant'], 400);
        }

        $cart = Cart::firstOrCreate(['client_id' => $request->user()->id]);

        // ✅ إذا المنتج موجود في السلة نزيد الكمية
        $item = CartItem::where('cart_id', $cart->id)
                        ->where('product_id', $product->id)
                        ->first();

        if ($item) {
            $item->update(['quantity' => $item->quantity + $request->quantity]);
        } else {
            CartItem::create([
                'cart_id'    => $cart->id,
                'product_id' => $product->id,
                'quantity'   => $request->quantity,
                'unit_price' => $product->price, // نحفظ السعر الحالي
            ]);
        }

        return response()->json(['message' => 'Produit ajouté au panier']);
    }

    // ✅ تعديل كمية منتج في السلة
    public function updateItem(Request $request, $id)
    {
        $request->validate(['quantity' => 'required|integer|min:1']);

        $item = CartItem::findOrFail($id);

        // تحقق من المخزون
        if ($item->product->stock < $request->quantity) {
            return response()->json(['message' => 'Stock insuffisant'], 400);
        }

        $item->update(['quantity' => $request->quantity]);

        return response()->json(['message' => 'Quantité mise à jour']);
    }

    // ✅ حذف منتج من السلة
    public function removeItem(Request $request, $id)
    {
        $item = CartItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Produit retiré du panier']);
    }
}