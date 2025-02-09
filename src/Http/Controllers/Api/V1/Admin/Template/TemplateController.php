<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Template;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TemplateController extends Controller
{

    /**
     * @var array
     */
    private $config = [
        'welcome' => [
            'type' => 'text',
            'value' => '',
        ],
        'images' => [
            'type' => 'image',
            'value' => '',
        ],
    ];

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
        ->select('id', 'template_name', 'template_link','des','template_banner')
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

    /**
     * 
     * Add Template
     * 
     */
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

                // check the site_config table for the template_id
                // get the template last inserted id
                return response()->json(['message' => 'Template updated successfully','code'=>200]);
            }else{
                return response()->json(['message' => 'Template not updated','code'=>202]);
            }

        }else{

            $template = DB::table('template')->insertGetId([
                'template_name' => $template_name,
                'template_link' => $template_link,
                'des' => $des,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            if($template){
                // get the template last inserted id

                $site_config = DB::table('site_config')->where('template_id',$template)->first();
                if(is_null($site_config)){
                    $site_config = DB::table('site_config')->insert([
                        'template_id' => $template,
                        'site_logo' => '',
                        'site_ico' => '',
                        'home_banner' => null,
                        'recommend' => null,
                        'template_banner' => null,
                        'config' => json_encode($this->config),
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
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
        $config = $request->config;


        $home_banner = json_encode($home_banner);
        $recommend = json_encode($recommend);
       
        $template = DB::table('site_config')->where('template_id',$id)->update([
            'site_logo' => $site_logo,
            'site_ico' => $site_ico,
            'home_banner' => $home_banner,
            'recommend' => $recommend,
            'config' => $config,
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
            $template->template_banner = $template->template_banner;

            $template->recommend = json_decode($template->recommend);

            // the template config need mapp the $this->config data
            
            // Decode the template config
            $templateConfig = json_decode($template->config, true);

            // Merge the template config with $this->config, giving priority to $this->config
            $mergedConfig = array_merge($templateConfig, $this->config);

            // Encode the merged config back to JSON
            $template->config = $mergedConfig;


            return response()->json(['message' => 'Template found','code'=>200,'data'=>$template]);
        }else{
            return response()->json(['message' => 'Template not found','code'=>202]);
        }

    }




    public function productInfo(Request $request)
    {

      
      

    }


}
