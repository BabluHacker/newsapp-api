<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Newspaper extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];

    /*
     * Relations*/
    public function url(){
        return $this->hasMany('App\CategoryPaperUrl', 'newspaper_id');
    }
    public function news(){
        return $this->hasMany('App\News', 'newspaper_id');
    }

    /*
     * Rules & Messages*/
    static public function rules($id=NULL)
    {
        if ($id == null)
            return [
                'alias_name'    => 'required|unique:newspapers,alias_name,',
                'name'          => 'required',
                'bn_name'          => 'required',
                /*'logo_square'          => 'required',
                'logo_rectangle'          => 'required',*/
            ];
        else
            return [
                'alias_name'    => 'required|unique:newspapers,alias_name,'.$id,
                'name'          => 'required',
                'bn_name'          => 'required',
                /*'logo_square'          => 'required',
                'logo_rectangle'          => 'required',*/
            ];
    }

    static public function messages($id=NULL)
    {
        return [];
    }

    static public function search($request)
    {
        $params = $request->all();
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? Newspaper::select(explode(",", $params['fields'])):Newspaper::select();


        if(isset($params['name']) and $params['name']!="" and $params['name']!="null"){
            $query->where('name', 'like', $params['name']);
        }
        if(isset($params['bn_name']) and $params['bn_name']!="" and $params['bn_name']!="null"){
            $query->where('bn_name', 'like', $params['bn_name']);
        }
        if(isset($params['alias_name']) and $params['alias_name']!="" and $params['alias_name']!="null"){
            $query->where('alias_name', 'like', $params['alias_name']);
        }
        if(isset($params['logo_square']) and $params['logo_square']!="" and $params['logo_square']!="null"){
            $query->where('logo_square', 'like', $params['logo_square']);
        }
        if(isset($params['logo_rectangle']) and $params['logo_rectangle']!="" and $params['logo_rectangle']!="null"){
            $query->where('logo_rectangle', 'like', $params['logo_rectangle']);
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
