<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class TokenDevAccess extends Model
{

    protected $table="token_dev_access";
    protected $guarded = ['id', 'updated_at', 'created_at'];

}
