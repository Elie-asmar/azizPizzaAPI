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
        Schema::dropIfExists('tbl_user_login');
        Schema::create('tbl_user_login', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id('login_id');
            $table->string('login_clientcode', 25);
            $table->string('login_usercode', 25);
            $table->string('login_token', 2000);
            $table->dateTime('login_timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tbl_user_login');
    }
};
