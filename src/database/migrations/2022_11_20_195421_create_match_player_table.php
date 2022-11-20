<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::create('match_player', function (Blueprint $table) {
            $table->bigInteger('player_id');
            $table->bigInteger('match_id');
            $table->tinyInteger('agent_id');
            $table->smallInteger('level');
            $table->smallInteger('headshots');
            $table->smallInteger('bodyshots');
            $table->smallInteger('legshots');
            $table->integer('damage_given');
            $table->integer('damage_taken');
            $table->smallInteger('c_casts');
            $table->smallInteger('q_casts');
            $table->smallInteger('e_casts');
            $table->smallInteger('x_casts');
            $table->integer('session_playtime');
            $table->integer('score');
            $table->smallInteger('kills');
            $table->smallInteger('deaths');
            $table->smallInteger('assists');

            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('match_id')->references('id')->on('matches');
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('match_player');
    }
};
