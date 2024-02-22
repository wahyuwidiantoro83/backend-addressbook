<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contacts', function (Blueprint $table){
            $table->id();
            $table->string('name',50);
            $table->string('address')->nullable(true);
            $table->string('phone',13);
            $table->string('images')->nullable(true);
            $table->enum('type',['Mobile','Work','Home','Main','Work Fax']);
            $table->string('email')->nullable(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contacts');
    }
}
