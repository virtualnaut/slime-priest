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
        Schema::create('maps', function (Blueprint $table) {
            $table->tinyIncrements('id')->unique();
            $table->text('name');
            // $table->text('minimap');
        });

        DB::table('maps')->insert([
            ['name' => 'Bind'],
            ['name' => 'Haven'],
            ['name' => 'Split'],
            ['name' => 'Ascent'],
            ['name' => 'Icebox'],
            ['name' => 'Breeze'],
            ['name' => 'Fracture'],
            ['name' => 'Pearl']
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maps');
    }
};
