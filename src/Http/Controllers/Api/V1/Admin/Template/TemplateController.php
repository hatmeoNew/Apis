<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Template;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    /**
     * Resource class name.
     */
    public function getTemplateList(Request $request)
    {  
      
    $limit = $request->input('limit', 10);
    $page = $request->input('page', 1); 

    // 计算偏移量
    $offset = ($page - 1) * $limit;

    // 查询数据并应用分页
    $templates = DB::table('template')
        ->select('id', 'template_name', 'template_link','des')
        ->offset($offset)
        ->limit($limit)
        ->get();

    // 获取总记录数
    $total = DB::table('template')->count();

    // 构建响应数据
    $response = [
        'current_page' => $page,
        'per_page' => $limit,
        'total' => $total,
        'last_page' => ceil($total / $limit),
        'data' => $templates,
    ];

    return response()->json($response);
    }

    public function editTemplate($id, Request $request)
    {
        $template = DB::table('template')->where('id',$id)->first();
        return $template;
    }

    public function detailTemplate($id)
    {

      
        $template = DB::table('template')->where('id',$id)->first();

        if($template){
            return response()->json(['message' => 'Template found','code'=>200,'data'=>$template]);
        }else{
            return response()->json(['message' => 'Template not found','code'=>202]);
        }

        
    }


    public function delTemplate($id)
    {
        $template = DB::table('template')->where('id',$id)->delete();

        if($template){
            return response()->json(['message' => 'Template deleted successfully','code'=>200]);
        }else{
            return response()->json(['message' => 'Template not deleted','code'=>202]);
        }
    
    }



    public function addTemplate(Request $request)
    {

        $request->validate([
            'template_name' => 'required',
            'template_link' => 'required',
            'type' => 'required',
        ]);


        $template = $request->all();
        $type = $request->type;
        $template_id = $request->template_id;
        $template_name = $request->template_name;
        $template_link = $request->template_link;
        $des = $request->des;


        if($type==2){
            $template = DB::table('template')->where('id',$template_id)->update([
                'template_name' => $template_name,
                'template_link' => $template_link,
                'des' => $des,
                'updated_at' => now()
            ]);

            if($template){
                return response()->json(['message' => 'Template updated successfully','code'=>200]);
            }else{
                return response()->json(['message' => 'Template not updated','code'=>202]);
            }

        }else{

            $template = DB::table('template')->insert([
                'template_name' => $template_name,
                'template_link' => $template_link,
                'des' => $des,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if($template){
                return response()->json(['message' => 'Template added successfully','code'=>200]);
            }else{
                return response()->json(['message' => 'Template not added','code'=>202]);
            }
        }
    }



      /**
     * Resource class name.
     */
    public function getCmsDetail($id, Request $request)
    {  

        // $request->validate([
        //     'locale' => 'required',
        // ]);

        // $locale = $request->input('locale');
      
        // $list = DB::table('cms_page_translations')->where('locale',$locale)->select('id','page_title','url_key','html_content','locale')->get();

       $list = DB::table('cms_page_translations')->where('id',$id)->select('id','page_title','url_key','html_content','locale')->get()->toArray();
       

        // var_dump($id);exit;

       return $list;
    }



    
      /**
     * Resource class name.
     */
    public function getCmsList($locale)
    {  

        // print_r($locale);exit;
        // $request->validate([
        //     'locale' => 'required',
        // ]);

        // $locale = $request->input('locale');
      
        $list = DB::table('cms_page_translations')->where('locale',$locale)->select('id','page_title','url_key','html_content','locale')->get()->toArray();
       

        if($list){
            return response()->json(['message' => 'Cms found','code'=>200,'data'=>$list]);
        }else{
            return response()->json(['message' => 'Cms not found','code'=>202,'data'=>[]]);
        }

       return $list;
    }


    /**
     * 
     * edit the template content
     * 
     * @param Request $request
     * 
     * 
     */
    public function editTemplateContent(Request $request)
    {

        // echo 123;exit;
        $request->validate([
        
            'template_id'=>'required'
        ]);

        $id = $request->template_id;
        $site_logo = $request->site_logo;
        $site_ico = $request->site_ico;
        $home_banner = $request->home_banner;
        $recommend = $request->recommend;

        $template_banner = $request->template_banner;


        $home_banner = json_encode($home_banner);
        $recommend = json_encode($recommend);
       
        $template = DB::table('site_config')->where('template_id',$id)->update([
            'site_logo' => $site_logo,
            'site_ico' => $site_ico,
            'home_banner' => $home_banner,
            'recommend' => $recommend,
            'template_banner' => $template_banner,
            'created_at' => now()
        ]);

        if($template){
            return response()->json(['message' => 'Template updated successfully','code'=>200]);
        }else{
            return response()->json(['message' => 'Template not updated','code'=>202]);
        }
    }


    public function templateContent($id)
    {       
        $template = DB::table('site_config')->where('template_id',$id)->first();

        if($template){

            $template->home_banner = json_decode($template->home_banner);
            $template->template_banner = isset($template->template_banner) ? $template->template_banner : '';

            $template->recommend = json_decode($template->recommend);


            
        }else{
            $template = [];
        }

        return response()->json(['message' => 'Template found','code'=>200,'data'=>$template]);

    }

    public function productInfo(Request $request)
    {

    }


}
