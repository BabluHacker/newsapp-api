<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class Category extends Model
{

    protected $guarded = ['id', 'updated_at', 'created_at'];

    /*
     * Relations*/
    public function news(){
        return $this->hasMany('App\News', 'category_id');
    }
    public function url(){
        return $this->hasMany('App\CategoryPaperUrl', 'category_id');
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
        $limit  = 1000;
        $query  = isset($params['fields'])? Category::select(explode(",", $params['fields'])):Category::select();




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

        // only newspaper wise categories
        if(isset($params['newspaper_id']) and $params['newspaper_id']!="" and $params['newspaper_id']!="null"){
            $models = CategoryPaperUrl::where('newspaper_id', '=', $params['newspaper_id'])->get();
            $category_ids = $models->unique('category_id')->pluck('category_id')->toArray();
            $query->whereIn('id', $category_ids);
        }

        $query->where('is_active', '=', 'Yes');
        $query->orderBy('order_id');

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
