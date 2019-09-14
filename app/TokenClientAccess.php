<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class TokenClientAccess extends Model
{
    protected $table="token_client_access";
    protected $guarded = ['id', 'updated_at', 'created_at'];

}
