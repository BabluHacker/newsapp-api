<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class TokenApiKey extends Model
{

    protected $table="token_api_keys";
    protected $guarded = ['id', 'updated_at', 'created_at'];

    public static function rules($type=null)
    {
        if($type == 'change_plan')
            return [
                'approved' => 'required|in:true,false'
            ];
        else
            return [
                'debug' => 'required|in:true,false'
            ];
    }
    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? TokenApiKey::select(explode(",", $params['fields'])):TokenApiKey::select();


        if(isset($params['debug']) and $params['debug']!="" and $params['debug']!="null"){
            $query->where('debug', 'like', $params['debug']);
        }

        if(isset($order)){
            $query->orderBy($order);
        }

        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }


}
