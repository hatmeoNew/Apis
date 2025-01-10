<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Category\Repositories\CategoryRepository;
use NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Catalog\CategoryResource;

class CategoryController extends CatalogController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return CategoryRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return CategoryResource::class;
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function descendantCategories(Request $request)
    {
        $results = $this->getRepositoryInstance()->getVisibleCategoryTree($request->input('parent_id'));

        return $this->getResourceCollection($results);
    }


            
      /**
     * Resource class name.
     */
    public function getCmsList($locale)
    {  
        if($locale == 'gb' || $locale == 'us'){
            $locale = 'en';

        }

      
        $list = DB::table('cms_page_translations')->where('locale',$locale)->select('id','page_title','url_key','html_content','locale')->get()->toArray();
       

        if($list){
            return response()->json(['message' => 'Cms found','code'=>200,'data'=>$list]);
        }else{
            return response()->json(['message' => 'Cms not found','code'=>202,'data'=>[]]);
        }

       return $list;
    }

   /**
     * Resource class name.
     */
    public function getCmsDetail($url_key, Request $request)
    {  

        $locale = $request->input('locale');
        
     

       $list = DB::table('cms_page_translations')->where('url_key',$url_key)->where('locale',$locale)->select('id','page_title','url_key','html_content','locale')->get()->toArray();
       

      if($list){
            return response()->json(['message' => 'Cms found','code'=>200,'data'=>$list]);
        }else{
            return response()->json(['message' => 'Cms not found','code'=>202,'data'=>[]]);
        }

    }


    public function addEmail(Request $request){

        $email = $request->input('email');
        $channel_id = Core()->getCurrentChannel()->id;


     

    $res = DB::table('subscribers_list')->insert([
        'email' => $email,
        'channel_id' => $channel_id
    ]);


    if($res){
        return response()->json(['message' => 'Email added successfully','code'=>200]);


    }else{
        return response()->json(['message' => 'Email not added','code'=>202]);
    }

    }

}
