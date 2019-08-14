<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class TokenApiKey extends Model
{

    protected $table="token_api_keys";
    protected $guarded = ['id', 'updated_at', 'created_at'];

}
