<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_user_login extends Model
{
    use HasFactory;
    protected $table = 'tbl_user_login';
    protected $primaryKey = 'login_id';

    public $timestamps = false;
}
