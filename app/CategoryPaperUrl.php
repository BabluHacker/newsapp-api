<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class CategoryPaperUrl extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];

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
        if ($id == null) {
            return [
                'url'   => 'required|unique:category_paper_urls,url',
                'category_id' => 'required',
                'newspaper_id' => 'required',
            ];
        }
        else{
            return [
                'url'   => 'required|unique:category_paper_urls,url,'.$id,
                'category_id' => 'required',
                'newspaper_id' => 'required',
            ];
        }
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

        if(isset($params['with']) and $params['with']!="" and $params['with']!="null"){
            $withs = explode('!', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }

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
