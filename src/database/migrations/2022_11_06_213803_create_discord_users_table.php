<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDiscordUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('discord_users', function (Blueprint $table) {
            $table->id();
            $table->tinyText('discord_id');
            $table->tinyText('role');
            $table->text('note')->nullable();
            $table->bigInteger('person_of_interest_id')->nullable();
            $table->timestamps();

            $table->index('person_of_interest_id');
            $table->foreign('person_of_interest_id')->references('id')->on('people_of_interest')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('discord_users');
    }
};
