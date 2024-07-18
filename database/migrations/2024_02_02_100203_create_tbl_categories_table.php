<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('tbl_categories');
        Schema::create('tbl_categories', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('cat_id');
            $table->string('cat_client', 25);
            $table->string('cat_name', 200);
            $table->dateTime('cat_timestamp');
            $table->integer('cat_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_categories');
    }
};
