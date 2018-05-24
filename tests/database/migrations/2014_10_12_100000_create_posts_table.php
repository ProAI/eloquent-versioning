<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('latest_version');
            $table->string('title');
            $table->timestamp('created_at');
            $table->softDeletes();
        });

        Schema::create('posts_version', function(Blueprint $table) {
            $table->integer('ref_id')->unsigned();
            $table->integer('version')->unsigned();
            $table->text('content');
            $table->timestamp('updated_at');

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
        Schema::dropIfExists('posts');
        Schema::dropIfExists('posts_version');
    }
}
