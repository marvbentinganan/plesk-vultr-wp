<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id('server_id');
            $table->uuid('server_uid')->unique();
            $table->string('provider_id');
            $table->foreignId('customer_id')->references('customer_id')->on('customers');
            $table->ipAddress('ip_address');
            $table->string('default_password');
            $table->string('plan');
            $table->string('region');
            $table->string('status');
            $table->boolean('ipv4_reverse_dns')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servers');
    }
}
