<?php namespace App;


use App\Http\Controllers\GraphApiController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AccessToken extends Model
{
    protected $guarded = ['id','created_at','updated_at'];
    static public function rules($id=NULL)
    {

    }

}
?>
