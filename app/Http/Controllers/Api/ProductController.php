<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    // ✅ كل المنتجات — عام للجميع مع بحث وفلترة
    public function index(Request $request)
    {
        $query = Product::with(['category', 'vendeur:id,name,shop_name'])
                        ->where('is_active', true)
                        ->where('stock', '>', 0);

        // بحث بالاسم
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // فلترة بالفئة
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // فلترة بالسعر
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        return response()->json($query->paginate(12));
    }

    // ✅ منتج واحد بالتفصيل
    public function show($id)
    {
        $product = Product::with(['category', 'vendeur:id,name,shop_name'])
                          ->findOrFail($id);

        return response()->json($product);
    }

    // ✅ كل الفئات
    public function categories()
    {
        return response()->json(Category::all());
    }

    // ✅ منتجات البائع الحالي فقط
    public function myProducts(Request $request)
    {
        $products = Product::with('category')
                           ->where('vendeur_id', $request->user()->id)
                           ->latest()
                           ->paginate(10);

        return response()->json($products);
    }

    // ✅ إضافة منتج جديد
    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image'       => 'nullable|image|max:2048', // max 2MB
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            // ✅ رفع الصورة إلى storage/app/public/products
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'vendeur_id'  => $request->user()->id,
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => $request->price,
            'stock'       => $request->stock,
            'category_id' => $request->category_id,
            'image'       => $imagePath,
            'is_active'   => true,
        ]);

        return response()->json($product, 201);
    }

    // ✅ تعديل منتج
    public function update(Request $request, $id)
    {
        $product = Product::where('id', $id)
                          ->where('vendeur_id', $request->user()->id)
                          ->firstOrFail();
        // firstOrFail يمنع البائع من تعديل منتجات بائع آخر

        $request->validate([
            'name'        => 'sometimes|string|max:255',
            'price'       => 'sometimes|numeric|min:0',
            'stock'       => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'is_active'   => 'sometimes|boolean',
            'image'       => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // ✅ حذف الصورة القديمة قبل رفع الجديدة
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $product->image = $request->file('image')->store('products', 'public');
        }

        $product->update($request->except('image'));
        $product->save();

        return response()->json($product);
    }

    // ✅ حذف منتج
    public function destroy(Request $request, $id)
    {
        $product = Product::where('id', $id)
                          ->where('vendeur_id', $request->user()->id)
                          ->firstOrFail();

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return response()->json(['message' => 'Produit supprimé']);
    }
}