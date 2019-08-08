<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class News extends Model
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
        $query  = isset($params['fields'])? News::select(explode(",", $params['fields'])):News::select();


        if(isset($params['news_type']) and $params['news_type']!="" and $params['news_type']!="null"){
            $query->where('news_type', 'like', $params['news_type']);
        }
        if(isset($params['newspaper_id']) and $params['newspaper_id']!="" and $params['newspaper_id']!="null"){
            $query->where('newspaper_id', 'like', $params['newspaper_id']);
        }
        if(isset($params['category_id']) and $params['category_id']!="" and $params['category_id']!="null"){
            $query->where('category_id', 'like', $params['category_id']);
        }
        if(isset($params['tag_id']) and $params['tag_id']!="" and $params['tag_id']!="null"){
            $query->whereRaw("FIND_IN_SET('".$params['tag_id']."', tag_ids)");
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