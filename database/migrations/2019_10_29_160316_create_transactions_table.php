<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use App\Transaction;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('ref')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('description')->nullable();
            $table->string('source_type')->nullable();
            $table->string('reference_order')->nullable();
            $table->string('charge_id')->nullable();
            $table->string('order_id')->nullable();
            $table->string('transaction_state')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status_id')->nullable();
            $table->string('checksum_status',1)->nullable(); //s=success, f=failed
            $table->string('notify_result')->nullable();
            $table->string('callback_result')->nullable();
            $table->string('inquiry_result')->nullable();
            $table->timestamps();
        });

        // $sql = "SET IDENTITY_INSERT transactions ON";
        // DB::statement($sql);

        // $sql = "ALTER TABLE transactions ALTER COLUMN id  IDENTITY (1100,1)";
        // DB::statement($sql);

        $sql = "ALTER TABLE transactions ALTER COLUMN notify_result NVARCHAR(MAX)";
        DB::statement($sql);

        $sql = "ALTER TABLE transactions ALTER COLUMN callback_result NVARCHAR(MAX)";
        DB::statement($sql);

        $sql = "ALTER TABLE transactions ALTER COLUMN inquiry_result NVARCHAR(MAX)";
        DB::statement($sql);




    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
}
