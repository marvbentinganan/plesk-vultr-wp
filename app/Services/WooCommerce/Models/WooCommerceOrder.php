<?php

namespace App\Services\WooCommerce\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WooCommerceOrder extends Model
{
    use SoftDeletes;

    protected $table = 'woocommerce_orders';

    protected $primaryKey = 'woocommerce_order_id';

    protected $guarded = ['woocommerce_order_id'];

    public function getDataAttribute($value)
    {
        return json_decode($value);
    }
}
