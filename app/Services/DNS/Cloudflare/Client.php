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

    /**
     * Get Zone ID from Cloudflare
     *
     * @return string
     */
    public function getZoneId()
    {
        return $this->zoneId;
    }

    /**
     * List DNS entries from Cloudflare
     *
     * @return Response
     */
    public function listRecords()
    {
        return $this->client->listRecords($this->zoneId);
    }

    /**
     * Get the record's ID
     *
     * @param string $type
     * @param string $domain
     * @return string
     */
    public function getRecordID(string $type, string $domain)
    {
        return $this->client->getRecordID($this->zoneId, $type, $domain);
    }

    /**
     * Add new DNS Record to Cloudflare
     *
     * @param string $type
     * @param string $name
     * @param string $content
     * @param integer $ttl
     * @param boolean $proxied
     * @return Response
     */
    public function addRecords(string $type, string $name, string $content, int $ttl = 0, bool $proxied = true)
    {
        return $this->client->addRecord($this->zoneId, $type, $name, $content, $ttl, $proxied);
    }

    /**
     * Update a DNS record in Cloudflare
     *
     * @param string $recordId
     * @param array $details
     * @return Response
     */
    public function updateRecord(string $recordId, array $details)
    {
        return $this->client->updateRecordDetails($this->zoneId, $recordId, $details);
    }
}
