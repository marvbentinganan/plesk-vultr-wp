<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PleskInstance extends Model
{
    protected $table = 'plesk_instances';

    protected $primaryKey = 'plesk_instance_id';

    protected $guarded = ['plesk_instance_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }
}
