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
        Schema::dropIfExists('tbl_groups');
        Schema::create('tbl_groups', function (Blueprint $table) {
            $table->id('grp_id');
            $table->unsignedBigInteger('grp_catid');
            $table->string('grp_name');
            $table->dateTime('grp_timestamp');
            $table->integer('grp_order')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_groups');
    }
};
