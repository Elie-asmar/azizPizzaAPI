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
        Schema::dropIfExists('tbl_items');
        Schema::create('tbl_items', function (Blueprint $table) {
            $table->id('itm_id');
            $table->unsignedBigInteger('itm_grpid');
            $table->string('itm_name', 200);
            $table->string('itm_description', 2000)->nullable();
            $table->decimal('itm_price', 18, 2);
            $table->string('itm_photo', 200)->nullable();
            $table->dateTime('itm_timestamp');
            $table->integer('itm_order')->nullable();
            $table->string('itm_status', 1)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_items');
    }
};
