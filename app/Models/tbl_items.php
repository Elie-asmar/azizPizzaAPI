<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class tbl_items extends Model
{
    use HasFactory;
    protected $table = 'tbl_items';
    protected $primaryKey = 'itm_id';

    public $timestamps = false;
}
