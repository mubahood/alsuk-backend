<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailThingsToOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
        $form->radio('order_state', __('Order State'))
        ->options([
            0 => 'Pending',
            1 => 'Processing',
            2 => 'Completed',
            3 => 'Canceled',
            4 => 'Failed',
        ]);         
         */
        Schema::table('orders', function (Blueprint $table) {
            $table->string('pending_mail_sent')->default('No')->nullable();
            $table->string('processing_mail_sent')->default('No')->nullable();
            $table->string('completed_mail_sent')->default('No')->nullable();
            $table->string('canceled_mail_sent')->default('No')->nullable();
            $table->string('failed_mail_sent')->default('No')->nullable();
            $table->bigInteger('sub_total')->default(0)->nullable();
            $table->bigInteger('tax')->default(0)->nullable();
            $table->bigInteger('discount')->default(0)->nullable();
            $table->bigInteger('delivery_fee')->default(0)->nullable();
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
            //
        });
    }
}
