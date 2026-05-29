<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'client_id', 'vendeur_id', 'livreur_id',
        'status', 'delivery_address',
        'total_amount', 'delivery_fee', 'commission',
        'notes', 'delivered_at'
    ];

    protected $casts = [
        'delivered_at'  => 'datetime',
        'total_amount'  => 'decimal:2',
        'delivery_fee'  => 'decimal:2',
        'commission'    => 'decimal:2',
    ];

    // ✅ العلاقات
    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }
    public function livreur()
    {
        return $this->belongsTo(User::class, 'livreur_id');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
    public function payment()
    {
        return $this->hasOne(Payment::class);
    }

    // ✅ ما يصل للبائع بعد خصم العمولة والتوصيل
    public function getVendeurAmount(): float
    {
        return $this->total_amount - $this->commission - $this->delivery_fee;
    }

    // ✅ تحقق إن الطلب قابل للإلغاء
    public function isCancellable(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']);
    }
}