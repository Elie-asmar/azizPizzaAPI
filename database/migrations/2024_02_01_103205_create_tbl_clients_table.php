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
        Schema::dropIfExists('tbl_clients');
        Schema::create('tbl_clients', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('clt_code', 25)->primary();
            $table->string('clt_name', 200);
            $table->string('clt_phone', 200)->nullable();
            $table->string('clt_address', 200)->nullable();
            $table->string('clt_menutitle', 200)->nullable();
            $table->string('clt_menulogo', 200)->nullable();
            $table->string('clt_email', 200)->nullable();
            $table->string('clt_fb', 200)->nullable();
            $table->string('clt_insta', 200)->nullable();
            $table->string('clt_whatsapp', 200)->nullable();
            $table->string('clt_menuurl', 200)->nullable();
            $table->dateTime('clt_serviceexpiry');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_clients');
    }
};
