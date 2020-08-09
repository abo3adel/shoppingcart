<?php

use Abo3adel\ShoppingCart\Cart;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items' . Cart::tbAddon(), function (Blueprint $table) {
            $table->unsignedBigInteger('id', true);

            // fix for laravel 5.5+
            if ((float) app()->version() < 5.8) {
                $table->integer('user_id', false, true);
            } else {
                // laravel 5.8+ uses
                $table->bigInteger('user_id', false, true);
            }

            $table->integer('qty', false, true);
            if (Cart::fopt()) {
                $table->string(Cart::fopt())->nullable();
            }
            if (Cart::sopt()) {
                $table->string(Cart::sopt())->nullable();
            }
            $table->float('price');
            $table->string('instance')->nullable()->default(
                Cart::defaultInstance()
            );
            $table->text('options')->nullable();
            $table->morphs('buyable');

            $table->foreign('user_id')
                ->references('id')
                ->on('users');

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
        Schema::dropIfExists('cart_items' . Cart::tbAddon());
    }
}
