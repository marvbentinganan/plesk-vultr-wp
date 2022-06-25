<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    protected $primaryKey = 'customer_id';

    protected $guarded = ['customer_id'];

    public function servers()
    {
        return $this->hasMany(Server::class, 'customer_id', 'customer_id');
    }

    public function plesk_instances()
    {
        return $this->hasMany(PleskInstance::class, 'customer_id', 'customer_id');
    }
}
