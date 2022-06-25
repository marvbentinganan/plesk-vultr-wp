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
            $table->string('api_key')->nullable();
            $table->string('temporary_domain')->nullable();
            $table->string('custom_domain')->nullable();
            $table->timestamps();
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
