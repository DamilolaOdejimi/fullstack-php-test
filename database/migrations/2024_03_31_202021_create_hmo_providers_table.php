<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHmoProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hmo_providers', function (Blueprint $table) {
            $table->id();
            $table->integer('hmo_id');
            $table->foreign('hmo_id')->references('id')->on('hmos')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('provider_id');
            $table->foreign('provider_id')->references('id')->on('providers')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('hmo_providers');
    }
}
