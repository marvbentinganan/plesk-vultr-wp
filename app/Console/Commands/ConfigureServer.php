<?php

namespace App\Console\Commands;

use App\Mail\SetupComplete;
use App\Models\Domain;
use App\Services\Plesk\AdminClient;
use App\Services\Plesk\Client;
use App\Services\Plesk\Models\PleskInstance;
use App\Services\Vultr\Client as VultrClient;
use App\Services\Vultr\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Ssh\Ssh;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ConfigureServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vp:configure-server
                            {--domainUid=}
                            {--validateDNS=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Configure Plesk instance.';

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
        $domain = Domain::where('domain_uid', $this->option('domainUid'))->first();

        $customer = $domain->customer;
        $server = $domain->server;

        if ($this->option('validateDNS') == true) {
            $valid = (gethostbyname($domain->name) == $server->ip_address);
            if ($valid == false) {
                $this->error("It looks like your Domain does not resolve to your server's IP Address.");

                return Command::FAILURE;
            }
        }

        // Generate API Key
        if (!$server->plesk_instance()->exists()) {
            $pleskAdminClient = new AdminClient($server->ip_address, $server->default_password);
            $this->line('Generating Plesk API Key.');
            $apiKey = $pleskAdminClient->createApiKey()->collect();

            $plesk = PleskInstance::create([
                    'plesk_instance_uid' => Str::uuid(),
                    'server_id' => $server->getKey(),
                    'customer_id' => $customer->getKey(),
                    'api_key' => $apiKey['key'],
                ]);

            $domain->update([
                'plesk_instance_id' => $plesk->getKey()
            ]);
        } else {
            $plesk = $server->plesk_instance;
        }

        $pleskClient = new Client($server->ip_address, $plesk->api_key);

        // Configure Panel URL and Login
        if ($plesk->custom_panel == false) {
            $this->line('Setting Up Panel');
            $initialize = $pleskClient->initialize($customer->name, $customer->email, config('services.plesk.panel_password'), $domain->panel);
            $this->line('Updating Panel Hostname.');
            $pleskClient->setHostname($domain->panel);
            $plesk->update([
                'custom_panel' => true
            ]);
            $this->info('Hostname Updated.');
            sleep(5);
        }

        // Add Domain to Plesk
        if ($plesk->attached_domain == false) {
            $this->line('Adding Domain to Panel.');
            $addDomain = $pleskClient->addDomain($domain->name);
            $domain->update([
                'guid' => $addDomain->collect()['guid']
            ]);
            $plesk->update([
                'attached_domain' => true
            ]);
            $this->info("Domain {$domain->name} added to Plesk.");
        }

        // Add SSL Certificate to Plesk Panel
        if ($plesk->panel_certificate == false) {
            $this->line('Securing Panel with SSL Certificate.');
            rescue(function () use ($pleskClient, $plesk, $customer, $server, $domain) {
                $apc = $pleskClient->addPanelCertificate($domain->name, sprintf('%s@%s', $customer->username, $domain->name));
                if ($apc->collect()['code'] == 0) {
                    $pleskClient->setPanelCertificate($server->ip_address);
                    $pleskClient->setMailserverCertificate();
                    $pleskClient->enableKeepSecured();
                    $plesk->update([
                        'panel_certificate' => true
                    ]);
                    $this->info('Panel Secured with an SSL Certificate.');
                } else {
                    $this->error('Unable to Add SSL Certificate to Panel.');

                    throw new HttpException(500, 'An error from the API occured.');
                }
            }, function () use ($plesk) {
                $plesk->update([
                    'panel_certificate' => false
                ]);
            });
        }

        // Add SSL Certificate to domain
        if ($domain->ssl_certificate == false) {
            $this->line('Securing Site with SSL Certificate');
            rescue(function () use ($pleskClient, $customer, $domain) {
                $adc = $pleskClient->addDomainCertificate(sprintf('%s@%s', $customer->username, $domain->name), $domain->name);
                if ($adc->collect()['code'] == 0) {
                    $domain->update([
                        'ssl_certificate' => true
                    ]);
                    $this->info('Primary Domain secured with SSL Certificate');
                    // Improve SSL Settings
                    if ($domain->improved_ssl == false) {
                        $this->line('Improving SSL Settings');

                        $hsts = $pleskClient->enableHSTS($domain->name)->collect();
                        $this->info("{$hsts['stdout']}");
                        $ocsp = $pleskClient->enableOCSP($domain->name)->collect();
                        $this->info("{$ocsp['stdout']}");

                        $domain->update([
                            'improved_ssl' => true
                        ]);
                    }
                } else {
                    $this->error('Unable to Add SSL Certificate to Primary Domain.');

                    throw new HttpException(500, 'An error from the API occured.');
                }
            }, function () use ($domain) {
                $domain->update([
                    'ssl_certificate' => false
                ]);
            });
        }

        // Install WordPress
        if ($plesk->wordpress_installed == false) {
            $this->line('Installing WordPress Site');
            rescue(function () use ($plesk, $pleskClient, $customer, $domain) {
                $install = $pleskClient->installWordPress(sprintf('%s@%s', $customer->username, $domain->name), $domain->name);
                if ($install->collect()['code'] == 0) {
                    $plesk->update([
                        'wordpress_installed' => true
                    ]);
                    $this->info('WordPress Installed');
                } else {
                    $this->error('Failed to Install WordPress.');

                    throw new HttpException(500, 'An error from the API occured.');
                }
            }, function () use ($plesk) {
                $plesk->update([
                    'wordpress_installed' => false
                ]);
            });
        }

        // Enable Caching
        if ($domain->caching_enabled == false) {
            $this->line('Enabling Nginx Cache');
            $pleskClient->enableCaching($domain->name);
            $domain->update([
                    'caching_enabled' => true
                ]);
            $this->info('Caching Enabled');
        }

        // Update Reverse DNS
        if ($server->ipv4_reverse_dns == false) {
            $this->line('Updating Reverse DNS');
            $vultr = new VultrClient();
            $vultr->updateReverseDNS($server->provider_id, $server->ip_address, $domain->name);
            $server->update([
                'ipv4_reverse_dns' => true
            ]);
        }

        // Install Firewall
        if ($plesk->firewall_installed == false) {
            $this->line('Installing Firewall Component');
            $command = 'plesk installer add --components psa-firewall';
            $process = Ssh::create('root', $server->ip_address)->disableStrictHostKeyChecking()->execute($command);

            $plesk->update([
                'firewall_installed' => true
            ]);

            $this->info('Plex Firewall Installed');
        }

        $status = $this->checkStatus($server, $plesk, $domain);

        if ($status == true) {
            $domain->update([
                'status' => 'active',
                'processed_at' => now()
            ]);
            $this->info('Configuration Complete!');
            Mail::to(env('ADMIN_EMAIL'))->send(new SetupComplete($domain, $customer));
        } else {
            $this->error('Some Processes Failed. Configuration Incomplete.');
        }

        return Command::SUCCESS;
    }

    public function checkStatus(Server $server, PleskInstance $plesk, Domain $domain)
    {
        if ($plesk->custom_panel == true
            and $plesk->attached_domain == true
            and $plesk->panel_certificate == true
            and $plesk->wordpress_installed == true
            and $plesk->firewall_installed == true
            and $domain->ssl_certificate == true
            and $domain->improved_ssl == true
            and $domain->caching_enabled == true
            and $server->ipv4_reverse_dns == true
            ) {
            return true;
        }

        return false;
    }
}
