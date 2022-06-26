<?php

namespace App\Models;

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

    public function domains()
    {
        return $this->hasMany(Domain::class, 'server_id', 'server_id');
    }
}
