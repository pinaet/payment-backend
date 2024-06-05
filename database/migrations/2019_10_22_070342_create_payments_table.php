<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Payment;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        $attributes['type'] = 'alipay';   $attributes['description'] = 'kbank';     Payment::create($attributes);
        $attributes['type'] = 'unionpay'; $attributes['description'] = 'kbank';     Payment::create($attributes);
        $attributes['type'] = 'wechat';   $attributes['description'] = 'kbank';     Payment::create($attributes);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
