<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->uuid('external_id');
            $table->char('mode');
            $table->smallInteger('map_id');
            $table->integer('length');
            $table->timestamp('started_at');
            $table->smallInteger('rounds_played');
            $table->text('region');
            $table->text('cluster');
            $table->char('outcome');
            $table->smallInteger('ally_rounds_won');
            $table->smallInteger('ally_rounds_lost');
            $table->smallInteger('enemy_rounds_won');
            $table->smallInteger('enemy_rounds_lost');

            $table->foreign('map_id')->references('id')->on('maps');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('matches');
    }
};
