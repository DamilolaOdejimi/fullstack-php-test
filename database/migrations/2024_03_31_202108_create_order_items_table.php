<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->string('item_name');
            $table->integer('order_id')->unsigned();
            $table->foreign('order_id')->references('orders')->on('id')
                ->onUpdate('cascade')->onDelete('cascade');
            $table->integer('quantity')->unsigned();
            $table->double('unit_price', 8, 2)->unsigned();
            $table->double('amount', 8, 2)->unsigned();
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
        Schema::dropIfExists('order_items');
    }
}
