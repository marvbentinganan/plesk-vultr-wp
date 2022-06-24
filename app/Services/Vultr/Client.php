<?php

namespace App\Services\Forge;

use Illuminate\Support\Facades\Http;

class LaravelForge
{
    protected $client;

    public function __construct()
    {
        $this->client = Http::acceptJson()
        ->withHeaders(
            [
                'Authorization' => sprintf('%s %s', 'Bearer', config('services.vultr.api'))
            ]
        );
    }
}
