<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchIdAndHmoProviderIdToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->integer('hmo_provider_id')->unsigned()->after('encounter_date');
            $table->foreign('hmo_provider_id')->references('id')->on('hmo_providers')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('batch_id')->nullable()->unsigned()->after('total_amount');
            $table->foreign('batch_id')->references('id')->on('batches')
                ->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign('hmo_provider_id');
            $table->dropForeign('batch_id');
        });
    }
}
