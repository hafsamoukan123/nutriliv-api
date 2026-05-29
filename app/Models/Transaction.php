<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'vendeur_id', 'order_id', 'amount',
        'commission_deducted', 'net_amount', 'type', 'status'
    ];

    protected $casts = [
        'amount'               => 'decimal:2',
        'commission_deducted'  => 'decimal:2',
        'net_amount'           => 'decimal:2',
    ];

    public function vendeur()
    {
        return $this->belongsTo(User::class, 'vendeur_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}