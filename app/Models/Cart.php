<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['client_id'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    // ✅ مجموع السلة
    public function getTotal(): float
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->unit_price);
    }

    // ✅ تفريغ السلة بعد تأكيد الطلب
    public function clear(): void
    {
        $this->items()->delete();
    }
}