<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDomainsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id('domain_id');
            $table->string('guid')->nullable();
            $table->string('name');
            $table->string('panel');
            $table->string('webmail');
            $table->boolean('ssl_certificate')->default(false);
            $table->boolean('caching_enabled')->default(false);
            $table->boolean('improved_ssl')->default(false);
            $table->foreignId('customer_id')->references('customer_id')->on('customers');
            $table->unsignedBigInteger('server_id')->nullable();
            $table->unsignedBigInteger('plesk_instance_id')->nullable();
            $table->string('status')->default('pending');
            $table->timestamp('processed_at')->nullable();
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
        Schema::dropIfExists('domains');
    }
}
