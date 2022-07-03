<?php

namespace App\Services\WooCommerce;

use Illuminate\Support\Facades\Http;

class Client
{
    protected $client;
    protected $apiEndpoint;

    public function __construct()
    {
        $this->client = Http::withBasicAuth(config('services.woocommerce.key'), config('services.woocommerce.secret'))
        ->withOptions(['verify' => false])
        ->retry(3, 2);

        $this->apiEndpoint = config('services.woocommerce.endpoint');
    }

    /**
     * Get Paid Orders from WooCommerce
     *
     * @param array $payload
     * @return Response
     */
    public function getPaidOrders($payload = ['status' => 'processing'])
    {
        return $this->client->get("{$this->apiEndpoint}/orders", $payload);
    }

    /**
     * Mark order as Completed in WooCommerce
     *
     * @param Integer $orderId
     * @param array $payload
     * @return Response
     */
    public function completeOrder($orderId, $payload = ['status' => 'completed'])
    {
        return $this->client->put("{$this->apiEndpoint}/orders/{$orderId}", $payload);
    }
}
