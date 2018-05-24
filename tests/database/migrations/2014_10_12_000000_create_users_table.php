<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('latest_version');
            $table->string('username');
            $table->timestamp('created_at');
        });

        Schema::create('users_version', function(Blueprint $table) {
            $table->integer('ref_id')->unsigned();
            $table->integer('version')->unsigned();
            $table->string('email');
            $table->string('city');
            $table->timestamp('updated_at');
            $table->softDeletes();

            $table->primary(['ref_id', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('users_version');
    }
}
