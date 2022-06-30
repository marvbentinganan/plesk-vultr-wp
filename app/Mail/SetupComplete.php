<?php

namespace App\Mail;

use App\Models\Customer;
use App\Models\Domain;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SetupComplete extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected $domain;
    protected $customer;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Domain $domain, Customer $customer)
    {
        $this->domain = $domain;
        $this->customer = $customer;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.system.complete')->with([
            'customer' => $this->customer,
            'panel_url' => sprintf('%s://%s:%s/%s', 'https', $this->domain->panel, 8443, 'login'),
            'panel_username' => 'admin',
            'panel_password' => config('services.plesk.panel_password'),
            'site_url' => sprintf('%s://%s', 'https', $this->domain->name),
            'site_email' => sprintf('%s@%s', $this->customer->username, $this->domain->name),
            'site_password' => config('services.plesk.wordpress_password')
        ]);
    }
}
