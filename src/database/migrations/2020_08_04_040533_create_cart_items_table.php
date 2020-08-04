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
        Schema::create(Cart::tbAddon() . 'cart_items', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('product_id');
            $table->integer('qty', false, true);
            if (Cart::fopt()) {
                $table->string(Cart::fopt());
            }
            if (Cart::sopt()) {
                $table->string(Cart::sopt());
            }
            $table->float('price');
            $table->string('instance')->nullable();
            $table->text('options');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade');

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
        Schema::dropIfExists(Cart::tbAddon() . 'cart_items');
    }
}
