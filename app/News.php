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
    protected $hidden = ['article', 'updated_at', 'created_at'];

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
        if(isset($params['with']) and $params['with']!="" and $params['with']!="null"){
            $withs = explode('!', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }

        if(isset($params['news_type']) and $params['news_type']!="" and $params['news_type']!="null"){
            $query->where('news_type', 'like', $params['news_type']);
        }
        if(isset($params['lang']) and $params['lang']!="" and $params['lang']!="null"){
            if($params['lang'] != 'both'){
                $query->where('lang', 'like', $params['lang']);
            }

        }
        if(isset($params['newspaper_id']) and $params['newspaper_id']!="" and $params['newspaper_id']!="null"){
            $query->where('newspaper_id', 'like', $params['newspaper_id']);
        }
        if(isset($params['category_id']) and $params['category_id']!="" and $params['category_id']!="null"){
            // coronavirus only section
            if($params['category_id'] == 15){
                // search tag also -> 16
                $query->where(function ($q) use ($params){
                    $q->whereNotNull('tag_ids->16')
                        ->orWhere('category_id', 'like', $params['category_id']);
                });
            }
            else{
                if ($params['category_id'] == 1){ // Top Picks

                }
                else {
                    $query->where('category_id', 'like', $params['category_id']);
                }
            }

        }
        if(isset($params['tag_id']) and $params['tag_id']!="" and $params['tag_id']!="null"){
            // coronavirus special
            if($params['tag_id'] == 16){
                $query->where(function ($q) use ($params){
                    $q->whereNotNull('tag_ids->'.$params['tag_id'])
                        ->orWhere('category_id', 'like', 15);
                });
            }
            else{
                $query->whereNotNull('tag_ids->'.$params['tag_id']);
            }
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
        if(isset($params['with']) and $params['with']!="" and $params['with']!="null"){
            $withs = explode('!', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }
        $query->where('id', '<>', $news_id);
        if(isset($params['lang']) and $params['lang']!="" and $params['lang']!="null"){
            if($params['lang'] != 'both'){
                $query->where('lang', 'like', $params['lang']);
            }
        }
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

    static public function global_search($request){
        $params = $request->all();
        if(isset($params['limit'])) $params['limit'] = $params['limit']>100 ? 100: $params['limit'];
        $limit  = isset($params['limit']) ? $params['limit'] : 10;
        $query  = isset($params['fields'])? News::select(explode(",", $params['fields'])):News::select();
        if(isset($params['with']) and $params['with']!="" and $params['with']!="null"){
            $withs = explode('!', $params['with']);
            foreach ($withs as $with){
                $query->with($with);
            }
        }

        /*
         * tag (category also included)
         * (now)keywords
         * (now)date_range
         *
         * newspaper_id
         *
         * */
        if(isset($params['keywords']) and $params['keywords']!="" and $params['keywords']!="null"){
            $keywords = array_filter(explode(',', $params['keywords']));
            $sec_keywords = $keywords;
            $tags = Tag::where(function ($q) use ($sec_keywords){
                $is_eng = !preg_match('/[^A-Za-z0-9]/', $sec_keywords[0]);
                if($is_eng) $q->where('keywords', 'like', '%'.$sec_keywords[0].'%');
                else $q->where('bn_keywords', 'like', '%'.$sec_keywords[0].'%');

                unset($sec_keywords[0]);
                foreach ($sec_keywords as $sec_keyword) {
                    $is_eng = preg_match('/[^A-Za-z0-9]/', $sec_keyword);
                    if($is_eng) $q->orWhere('keywords', 'like', '%'.$sec_keyword.'%');
                    else $q->orWhere('bn_keywords', 'like', '%'.$sec_keyword.'%');
                }
            })->get()->pluck('id')->toArray();



            $query->where(function ($query) use ($keywords, $params, $tags){
                $query->where(function ($q) use ($tags){
                    foreach ($tags as $tag){
                        if($tag == 16){
                            $q->where(function ($q) use ($tag){
                                $q->whereNotNull('tag_ids->'.$tag)
                                    ->orWhere('category_id', 'like', 15);
                            });
                        }
                        else{
                            $q->whereNotNull('tag_ids->'.$tag);
                        }
                    }
                })->orWhere(function ($q) use ($params, $keywords){
                    $q->where('article', 'like', '%'.$keywords[0].'%');
                    unset($keywords[0]);
                    foreach ($keywords as $keyword) {
                        $q->orWhere('article', 'like', '%'.$keyword.'%');
                    }
                });
            });
        }


        if(isset($params['date_from']) and $params['date_from']!="" and $params['date_from']!="null"){
            $query->where('published_time', '>=', $params['date_from']);
        }
        if(isset($params['date_to']) and $params['date_to']!="" and $params['date_to']!="null"){
            $query->where('published_time', '<=', $params['date_to']);
        }

        $query->orderBy('published_time', 'desc');

        $data = $query->paginate($limit);

        return [
            'status'=>1,
            'data' => $data
        ];
    }

}
