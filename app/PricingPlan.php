<?php


namespace App;


use Illuminate\Database\Eloquent\Model;

class PricingPlan extends Model
{
    protected $guarded = ['id', 'updated_at', 'created_at'];


    /*
     * Rules & Messages*/
    static public function rules($id=NULL)
    {
        if ($id == null)
            return [

            ];
        else
            return [

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
        $query  = isset($params['fields'])? PricingPlan::select(explode(",", $params['fields'])):PricingPlan::select();


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
