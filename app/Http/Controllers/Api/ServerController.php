<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Server\ServerInterface;

class ServerController extends Controller
{
    protected $server;

    public function __construct(ServerInterface $server)
    {
        $this->server = $server;
    }

    public function create(Request $request)
    {
        $this->server->create($request->toArray());

        return response('Provisioning Server', 200);
    }

    public function delete($id)
    {
        $this->server->delete($id);

        return response('Deleting Server', 200);
    }
}
