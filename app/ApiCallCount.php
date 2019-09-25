<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class ApiCallCount extends Model
{
    protected $table="api_call_counts";
    protected $guarded = ['id', 'updated_at', 'created_at'];
}
