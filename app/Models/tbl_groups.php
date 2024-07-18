<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_groups extends Model
{
    use HasFactory;
    protected $table = 'tbl_groups';
    protected $primaryKey = 'grp_id';

    public $timestamps = false;
}
