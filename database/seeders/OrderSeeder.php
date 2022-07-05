<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $customer = Customer::updateOrCreate(
            [
                'email' => 'marvbentinganan@gmail.com'
            ],
            [
                'name' => 'Marvin Bentinganan',
                'customer_uid' => Str::uuid(),
                'email' => 'marvbentinganan@gmail.com',
                'username' => 'marviebenti',
                'company' => 'Mariner, LLC'
            ]
        );

        $customer->domains()->updateOrCreate(
            [
                'name' => 'elendil.uk'
            ],
            [
                'domain_uid' => Str::uuid(),
                'name' => 'marviebenti.com',
                'panel' => 'panel.marviebenti.com',
                'webmail' => 'webmail.marviebenti.com'
            ]
        );
    }
}
