<?php

namespace App\Services\Plesk\Models;

use App\Models\Customer;
use App\Models\Domain;
use App\Services\Vultr\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PleskInstance extends Model
{
    use SoftDeletes;

    protected $table = 'plesk_instances';

    protected $primaryKey = 'plesk_instance_id';

    protected $guarded = ['plesk_instance_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }

    public function domains()
    {
        return $this->hasMany(Domain::class, 'plesk_instance_id', 'plesk_instance_id');
    }
}
