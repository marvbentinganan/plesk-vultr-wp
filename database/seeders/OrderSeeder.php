<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

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
                'email' => 'marvbentinganan@gmail.com',
                'username' => 'marviebenti',
                'company' => 'Mariner, LLC'
            ]
        );

        $customer->domains()->updateOrCreate(
            [
                'name' => 'marviebenti.com'
            ],
            [
                'name' => 'marviebenti.com',
                'panel' => 'panel.marviebenti.com',
                'webmail' => 'webmail.marviebenti.com'
            ]
        );
    }
}
