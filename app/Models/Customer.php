<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

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

    public function domains()
    {
        return $this->hasMany(Domain::class, 'customer_id', 'customer_id');
    }
}
