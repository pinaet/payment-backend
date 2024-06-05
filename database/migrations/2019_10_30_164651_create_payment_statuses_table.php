<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\PaymentStatus;

class CreatePaymentStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('payment_status')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $attributes['payment_status'] = 'pending';     $attributes['description'] = 'wait for confirm';   PaymentStatus::create($attributes);
        $attributes['payment_status'] = 'completed';   $attributes['description'] = 'payment succeed';    PaymentStatus::create($attributes);
        $attributes['payment_status'] = 'declined';    $attributes['description'] = 'payment declined';   PaymentStatus::create($attributes);
        $attributes['payment_status'] = 'conflict';    $attributes['description'] = 'payment conflict';   PaymentStatus::create($attributes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_statuses');
    }
}
