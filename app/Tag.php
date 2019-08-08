<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = ['id'];

    /*
     * Relations*/


    /*
     * Rules & Messages*/
    static public function rules($id=NULL)
    {
        return [];
    }

    static public function messages($id=NULL)
    {
        return [];
    }

    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? Tag::select(explode(",", $params['fields'])):Tag::select();


        if(isset($params['name']) and $params['name']!="" and $params['name']!="null"){
            $query->where('name', 'like', $params['name']);
        }
        if(isset($params['keyword']) and $params['keyword']!="" and $params['keyword']!="null"){
            $query->whereRaw("FIND_IN_SET('".$params['keyword']."', keywords)");
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