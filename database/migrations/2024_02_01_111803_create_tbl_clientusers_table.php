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
        Schema::dropIfExists('tbl_clientusers');
        Schema::create('tbl_clientusers', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->string('usr_client', 25);
            $table->string('usr_usercode', 25);
            $table->string('usr_username', 200);
            $table->longText('usr_password');
            $table->primary(['usr_client', 'usr_usercode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_clientusers');
    }
};
