<?php

namespace App\Console\Commands;

use App\Models\Domain;
use App\Services\DNS\Cloudflare\Client;
use Illuminate\Console\Command;

class UpdateDNS extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:update-dns
                            {--domainId=}
                            {--ipAddress=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add additional DNS Entries';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $domain = Domain::find($this->option('domainId'));
        $ipAddress = $this->option('ipAddress');

        $dnsclient = new Client($domain->name);

        // Add/Update Primary DNS Record
        $primary = $dnsclient->getRecordID('A', $domain->name);
        if ($primary != '') {
            $this->line('Updating Primary DNS record');
            $details = collect([
                'type' => 'A',
                'name' => $domain->name,
                'content' => $ipAddress,
                'ttl' => 0,
                'proxied' => false
            ])->toArray();
            $dnsclient->updateRecord($primary, $details);
        } else {
            $this->line('Adding Primary DNS record');
            $dnsclient->addRecords('A', $domain->name, $ipAddress, 0, false);
        }

        // Add/Update Panel DNS Record
        $webmail = $dnsclient->getRecordID('A', $domain->webmail);
        if ($webmail != '') {
            $this->line('Updating Webmail DNS record');
            $details = collect([
                'type' => 'A',
                'name' => $domain->webmail,
                'content' => $ipAddress,
                'ttl' => 0,
                'proxied' => false
            ])->toArray();
            $dnsclient->updateRecord($webmail, $details);
        } else {
            $this->line('Adding Webmail DNS record');
            $dnsclient->addRecords('A', $domain->webmail, $ipAddress, 0, false);
        }

        // Add/Update Panel DNS Record
        $panel = $dnsclient->getRecordID('A', $domain->panel);
        if ($panel != '') {
            $this->line('Updating Custom Panel DNS record');
            $details = collect([
                'type' => 'A',
                'name' => $domain->panel,
                'content' => $ipAddress,
                'ttl' => 0,
                'proxied' => false
            ])->toArray();
            $dnsclient->updateRecord($panel, $details);
        } else {
            $this->line('Adding Custom Panel DNS record');
            $dnsclient->addRecords('A', $domain->panel, $ipAddress, 0, false);
        }

        // Add/Update Panel DNS Record
        $www = $dnsclient->getRecordID('CNAME', sprintf('%s.%s', 'www', $domain->name));
        if ($www != '') {
            $this->line('Updating CNAME DNS record');
            $details = collect([
                'type' => 'CNAME',
                'name' => 'www',
                'content' => $domain->name,
                'ttl' => 0,
                'proxied' => false
            ])->toArray();
            $dnsclient->updateRecord($www, $details);
        } else {
            $this->line('Adding CNAME DNS record');
            $dnsclient->addRecords('CNAME', 'www', $domain->name, 0, false);
        }

        sleep(5);

        $this->info('DNS Update Complete!');

        return Command::SUCCESS;
    }
}
