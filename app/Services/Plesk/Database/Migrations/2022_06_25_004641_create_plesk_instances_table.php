<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePleskInstancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plesk_instances', function (Blueprint $table) {
            $table->id('plesk_instance_id');
            $table->foreignId('server_id')->references('server_id')->on('servers');
            $table->foreignId('customer_id')->references('customer_id')->on('customers');
            $table->string('api_key')->nullable();
            $table->boolean('custom_panel')->default(false);
            $table->boolean('panel_certificate')->default(false);
            $table->boolean('attached_domain')->default(false);
            $table->boolean('wordpress_installed')->default(false);
            $table->boolean('firewall_installed')->default(false);
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
        Schema::dropIfExists('plesk_instances');
    }
}
