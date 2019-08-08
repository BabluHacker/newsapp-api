<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class CategoryPaperUrl extends Model
{
    protected $guarded = ['id'];

    /*
     * Relations*/
    public function category()
    {
        return $this->belongsTo('App\Category');
    }
    public function newspaper()
    {
        return $this->belongsTo('App\Newspaper');
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
        $query  = isset($params['fields'])? CategoryPaperUrl::select(explode(",", $params['fields'])):CategoryPaperUrl::select();

        if(isset($params['newspaper_id']) and $params['newspaper_id']!="" and $params['newspaper_id']!="null"){
            $query->where('newspaper_id', 'like', $params['newspaper_id']);
        }
        if(isset($params['category_id']) and $params['category_id']!="" and $params['category_id']!="null"){
            $query->where('category_id', 'like', $params['category_id']);
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