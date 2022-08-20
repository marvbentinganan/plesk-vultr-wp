<?php

namespace App\Repositories\Server;

use Illuminate\Support\Str;
use App\Services\Vultr\Models\Server;
use App\Repositories\Server\ServerInterface;

use App\Services\Vultr\Endpoints\Server as VultrServer;

class ServerRepository implements ServerInterface
{
    protected $vultrService;

    public function __construct(VultrServer $server)
    {
        $this->vultrService = $server;
    }

    public function create(array $data)
    {
        $response = $this->vultrService->create($data);

        $instance = $response->collect()['instance'];

        // Create Server record
        $server = Server::create([
            'server_uid' => Str::uuid(),
            'provider_id' => $instance['id'],
            'default_password' => $instance['default_password'],
            'hostname' => $instance['hostname'],
            'ip_address' => $instance['main_ip'],
            'plan' => $instance['plan'],
            'region' => $instance['region'],
            'status' => $instance['status']
        ]);
    }

    public function delete(int $id)
    {
        $server = Server::find($id);

        // Call Service to delete server in Vultr

        return $server->delete();
    }
}
