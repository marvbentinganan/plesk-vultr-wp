<?php

namespace App\Services\Vultr\Models;

use App\Models\Customer;
use App\Models\Domain;
use App\Services\Plesk\Models\PleskInstance;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Server extends Model
{
    use SoftDeletes;

    protected $table = 'servers';

    protected $primaryKey = 'server_id';

    protected $guarded = ['server_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function plesk_instance()
    {
        return $this->hasOne(PleskInstance::class, 'server_id', 'server_id');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'server_id', 'server_id');
    }
}
