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
        Schema::dropIfExists('tbl_menuitems');
        Schema::create('tbl_menuitems', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('menuItem_id', 25)->primary();
            $table->string('menuItem_name', 100);
            $table->string('menuItem_desc', 100);
            $table->string('menuItem_ingredient', 200);
            $table->string('menuItem_size', 25);
            $table->string('menuItem_category', 100);
            $table->string('menuItem_price', 25);
            $table->string('menuItem_img', 200);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_menuitems');
    }
};
