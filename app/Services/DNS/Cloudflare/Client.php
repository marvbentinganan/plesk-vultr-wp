<?php

namespace App\Services\DNS\Cloudflare;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;

class Client
{
    protected $client;
    protected $zoneId;
    protected $domain;

    public function __construct(string $domain)
    {
        $key = new APIKey(config('services.cloudflare.username'), config('services.cloudflare.api_key'));
        $adapter = new Guzzle($key);
        $zone = new Zones($adapter);
        $this->domain = $domain;
        $this->client = new DNS($adapter);
        $this->zoneId = $zone->getZoneID($domain);
    }

    public function getZoneId()
    {
        return $this->zoneId;
    }

    public function listRecords()
    {
        return $this->client->listRecords($this->zoneId);
    }

    public function getRecordID(string $type, string $domain)
    {
        return $this->client->getRecordID($this->zoneId, $type, $domain);
    }

    public function addRecords(string $type, string $name, string $content, int $ttl = 0, bool $proxied = true)
    {
        return $this->client->addRecord($this->zoneId, $type, $name, $content, $ttl, $proxied);
    }

    public function updateRecord(string $recordId, $details)
    {
        return $this->client->updateRecordDetails($this->zoneId, $recordId, $details);
    }
}
