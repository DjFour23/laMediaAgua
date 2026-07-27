<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // <-- 1. Importar

class Order extends Model
{

    use SoftDeletes; // <-- 2. Usar el Trait


    protected $fillable = [
        'user_id',
        'order_number',
        'sub_total',
        'quantity',
        'delivery_charge',
        'status',
        'total_amount',
        'first_name',
        'last_name',
        'country',
        'post_code',
        'address1',
        'address2',
        'phone',
        'email',
        'payment_method',
        'payment_status',
        'shipping_id',
        'coupon',
        'payment_reference' // <-- Agregado para soportar el comprobante de Bre-B
    ];

    public function cart_info()
    {
        return $this->hasMany('App\Models\Cart', 'order_id', 'id');
    }

    public static function getAllOrder($id)
    {
        return Order::with('cart_info')->find($id);
    }

    public static function countActiveOrder()
    {
        $data = Order::count();
        if ($data) {
            return $data;
        }
        return 0;
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'shipping_id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}