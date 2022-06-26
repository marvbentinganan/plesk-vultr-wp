<?php

namespace App\Models;

use App\Services\Plesk\Models\PleskInstance;
use App\Services\Vultr\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use SoftDeletes;

    protected $table = 'domains';

    protected $primaryKey = 'domain_id';

    protected $guarded = ['domain_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    public function plesk_instance()
    {
        return $this->belongsTo(PleskInstance::class, 'plesk_instance_id', 'plesk_instance_id');
    }

    public function server()
    {
        return $this->belongsTo(Server::class, 'server_id', 'server_id');
    }
}
