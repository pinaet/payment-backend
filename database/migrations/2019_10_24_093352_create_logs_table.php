<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type')->nullable();
            $table->string('log')->nullable();
            $table->string('log2')->nullable();
            $table->timestamps();
        });

        $sql = "ALTER TABLE logs ALTER COLUMN log NVARCHAR(MAX)";   DB::statement($sql);
        $sql = "ALTER TABLE logs ALTER COLUMN log2 NVARCHAR(MAX)";  DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('logs');
    }
}
