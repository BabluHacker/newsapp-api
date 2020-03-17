<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $casts = [
        'tag_ids' => 'json',
    ];
    protected $table="newses";
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
        return [];
    }

    static public function messages($id=NULL)
    {
        return [];
    }

    static public function search($request)
    {
        $params = $request->all();
        if(isset($params['limit'])) $params['limit'] = $params['limit']>100 ? 100: $params['limit'];
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? News::select(explode(",", $params['fields'])):News::select();
        if(isset($params['with'])){
            $withs = explode(',', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }

        if(isset($params['news_type']) and $params['news_type']!="" and $params['news_type']!="null"){
            $query->where('news_type', 'like', $params['news_type']);
        }
        if(isset($params['newspaper_id']) and $params['newspaper_id']!="" and $params['newspaper_id']!="null"){
            $query->where('newspaper_id', 'like', $params['newspaper_id']);
        }
        if(isset($params['category_id']) and $params['category_id']!="" and $params['category_id']!="null"){
            if ($params['category_id'] == 1){ // Top Picks

            }
            else {
                $query->where('category_id', 'like', $params['category_id']);
            }
        }
        if(isset($params['tag_id']) and $params['tag_id']!="" and $params['tag_id']!="null"){
            $query->whereRaw("FIND_IN_SET('".$params['tag_id']."', tag_ids)");
        }
        /** latest crawler id*/
        if(isset($params['last_news_date']) and $params['last_news_date']!="" and $params['last_news_date']!="null"){
            $query->where('published_time', '>', $params['last_news_date']);
        }

        $query->orderBy('published_time', 'desc');

        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }
    static public function related_search($request, $news_id){
        $modelNews = News::find($news_id);
        $params = $request->all();
        if(isset($params['limit'])) $params['limit'] = $params['limit']>100 ? 100: $params['limit'];
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? News::select(explode(",", $params['fields'])):News::select();
        if(isset($params['with'])){
            $withs = explode(',', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }
        $query->where('id', '<>', $news_id);

        if($modelNews->tag_ids == '{}'){
            $query->where('category_id', '=', $modelNews->category_id);
        }

        else{
            $query->whereRaw('JSON_CONTAINS(tag_ids, ?)', json_encode($modelNews->tag_ids));
        }

        $query->orderBy('published_time', 'desc');

        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];

    }

}
