<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Thiagoprz\CompositeKey\HasCompositeKey;

class tbl_clientusers extends Model
{
    use HasFactory;
    //this library is used to allow eloquent working with composite PKs
    use HasCompositeKey;
    //Specify the table that is linked to this model
    protected $table = 'tbl_clientusers';
    //specify the PK
    protected $primaryKey = ['usr_client', 'usr_usercode'];
    //If your model's primary key is not an integer, you should define a protected $keyType property on your model. This property should have a value of string
    // protected $keyType = 'string';
    // Indicates if the model's ID is auto-incrementing.
    public $incrementing = false;
    //Indicate to Eloquent not to include default columns updated_at and created_at in the
    //generated SQL statement
    public $timestamps = false;
}
