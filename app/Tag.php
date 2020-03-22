<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];

    /*
     * Relations*/


    /*
     * Rules & Messages*/
    static public function rules($id=NULL)
    {
        if ($id==null)
            return [
                'name' => 'required|unique:tags,name,',
                'keywords' => 'required'
            ];
        else
            return [
                'name' => 'required|unique:tags,name,',$id,
                'keywords' => 'required'
            ];
    }

    static public function messages($id=NULL)
    {
        return [];
    }

    static public function search($request)
    {
        $params = $request->all();
        $limit  = 1000;
        $query  = isset($params['fields'])? Tag::select(explode(",", $params['fields'])):Tag::select();


        if(isset($params['name']) and $params['name']!="" and $params['name']!="null"){
            $query->where('name', 'like', $params['name']);
        }
        if(isset($params['delete_request']) and $params['delete_request']!="" and $params['delete_request']!="null"){
            $query->where('delete_request', 'like', $params['delete_request']);
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
