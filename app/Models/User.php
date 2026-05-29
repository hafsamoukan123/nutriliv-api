<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'address',
        'shop_name', 'bank_account', 'balance',
        'vehicle_type', 'current_location', 'is_available'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
     protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_available' => 'boolean',
            'balance' => 'decimal:2',
        ];
    }

    // ✅ Helper functions للتحقق من الدور
    public function isClient()   { return $this->role === 'client'; }
    public function isVendeur()  { return $this->role === 'vendeur'; }
    public function isLivreur()  { return $this->role === 'livreur'; }
    public function isAdmin()    { return $this->role === 'admin'; }

    // ✅ علاقات الـ Vendeur
    public function products()
    {
        return $this->hasMany(Product::class, 'vendeur_id');
    }

    // ✅ علاقات الـ Client
    public function cart()
    {
        return $this->hasOne(Cart::class, 'user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'user_id');
    }

    // ✅ علاقات الـ Livreur
    public function deliveries()
    {
        return $this->hasMany(Order::class, 'livreur_id');
    }

    // ✅ الإشعارات
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

}
