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

        Schema::table('tbl_clientusers', function (Blueprint $table) {
            $table->foreign('usr_client')->references('clt_code')->on('tbl_clients');
        });
        Schema::table('tbl_categories', function (Blueprint $table) {
            $table->foreign('cat_client')->references('clt_code')->on('tbl_clients');
        });
        Schema::table('tbl_groups', function (Blueprint $table) {
            $table->foreign('grp_catid')->references('cat_id')->on('tbl_categories');
        });
        Schema::table('tbl_items', function (Blueprint $table) {
            $table->foreign('itm_grpid')->references('grp_id')->on('tbl_groups');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
