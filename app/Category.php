<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = ['id'];

    /*
     * Relations*/
    public function url(){
        return $this->hasMany('App\CategoryPaperUrl');
    }

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
        $query  = isset($params['fields'])? Category::select(explode(",", $params['fields'])):Category::select();


        if(isset($params['name']) and $params['name']!="" and $params['name']!="null"){
            $query->where('name', 'like', $params['name']);
        }
        if(isset($params['alias_name']) and $params['alias_name']!="" and $params['alias_name']!="null"){
            $query->where('alias_name', 'like', $params['alias_name']);
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