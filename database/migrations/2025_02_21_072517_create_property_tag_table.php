<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePropertyTagTable extends Migration
{
    public function up()
    {
        Schema::create('property_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');

            $table->unique(['property_id', 'tag_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('property_tag');
    }
}
