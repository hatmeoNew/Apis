<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Catalog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Repositories\ProductRepository;
use NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Catalog\ProductResource;

class ProductController extends CatalogController
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
        return ProductRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ProductResource::class;
    }

    public function slug($slug)
    {

        $product = $this->getRepositoryInstance()->findBySlug($slug);

        if (!$product) {
            return response()->json(['message' => 'Product not found', 'code' => 202, 'data' => []]);
        }

        return ProductResource::make($product);
    }

    /**
     * Returns a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function allResources(Request $request)
    {
        // support product name and price sorting
        if ($request->has('sort') && $request->get('sort') === 'name') {
            $request->merge(['sort' => 'name', 'order' => $request->get('order', 'asc')]);
        } elseif ($request->has('sort') && $request->get('sort') === 'price') {
            $request->merge(['sort' => 'price', 'order' => $request->get('order', 'asc')]);
        }

        $results = $this->getRepositoryInstance()->getAll($request->all());

        // $results = $this->getRepositoryInstance()->getAll($request->input('category_id'));

        return $this->getResourceCollection($results);
    }

    /**
     * Returns product's additional information.
     *
     * @return \Illuminate\Http\Response
     */
    public function additionalInformation(Request $request, int $id)
    {
        $resource = $this->getRepositoryInstance()->findOrFail($id);

        $additionalInformation = app(\Webkul\Product\Helpers\View::class)
            ->getAdditionalData($resource);

        return response([
            'data' => $additionalInformation,
        ]);
    }

    /**
     * Returns product's additional information.
     *
     * @return \Illuminate\Http\Response
     */
    public function configurableConfig(Request $request, int $id)
    {
        $resource = $this->getRepositoryInstance()->findOrFail($id);

        $configurableConfig = app(\Webkul\Product\Helpers\ConfigurableOption::class)
            ->getConfigurationConfig($resource);

        return response([
            'data' => $configurableConfig,
        ]);
    }



    public function getIndexContent($template_name)
    {
        // $template_id = $request->input('template_id');
        // $type = $request->input('type');

        $template_id = DB::table('template')->where('template_name', $template_name)->value('id');


        if (empty($template_id)) {
            return response()->json(['message' => 'Template not found', 'code' => 202, 'data' => []]);
        }

        $config_info = DB::table('site_config')->where('template_id', $template_id)->get()->toArray();
        $config_info = json_decode(json_encode($config_info), true);

        if (!empty($config_info)) {

            foreach ($config_info as $key => $value) {
                $config_info[$key]['home_banner'] = json_decode($value['home_banner'], true);
                $config_info[$key]['template_banner'] = isset($value['template_banner']) ? $value['template_banner'] : '';
                $config = isset($value['config']) ? json_decode($value['config'], true) : [];
                $config_info[$key]['config'] = $config;
                $recommend = json_decode($value['recommend'], true);

                // print_r($recommend);exit;

                if (!empty($recommend)) {

                    $recommend_product = [];
                    foreach ($recommend as $key1 => $value1) {



                        $product_info = DB::table('product_flat')->where('product_id', $value1['product_id'])->select('id', 'name', 'price', 'url_key')->first();


                        if (!empty($product_info)) {
                            $recommend_product[$key1]['product_id'] =  $value1['product_id'];
                            $recommend_product[$key1]['name'] = $product_info->name;
                            $recommend_product[$key1]['price'] = $product_info->price;
                            $recommend_product[$key1]['des'] = $value1['description'];
                            $recommend_product[$key1]['url_key'] = $product_info->url_key;
                        } else {
                            $recommend_product[$key1]['id'] = '';
                            $recommend_product[$key1]['name'] = '';
                            $recommend_product[$key1]['price'] = '';
                            $recommend_product[$key1]['des'] = '';
                            $recommend_product[$key1]['url_key'] = '';
                        }
                        // print_r($recommend_product);exit;


                        $recommend_product[$key1]['title'] = $value1['title'];
                        $recommend_product[$key1]['image'] = DB::table('product_images')->where('product_id', $value1['product_id'])->value('path');

                    }

                    $product_list = [];
                    if (!empty($config['tool_recommend'])) {
                        foreach ($config['tool_recommend'] as $product) {
                            $product_id = $product['product_id'];
                            $product = $this->getRepositoryInstance()
                                ->with(['images', 'super_attributes'])
                                ->find($product_id);

                            $product['product_package'] = $product->type == 'configurable' ? \Nicelizhi\OneBuy\Helpers\Utils::makeProducts($product, [2, 1, 3, 4]) : [];

                            $product_list[] = $product;
                        }
                    }

                    $config_info[$key]['tool_recommend_product_list'] = $product_list;

                    $config_info[$key]['recommend'] = $recommend_product;
                } else {
                    $config_info[$key]['recommend'] = [];
                }
            }

            // $config_info['product_list'] = $product_list;
            return response()->json($config_info);
        } else {
            return response()->json(['message' => 'Under Maintenance', 'code' => 202, 'data' => []]);
        }
    }

    /**
     *
     * Recommend product by product id
     *
     * @param int $product_id
     *
     * @return \Illuminate\Http\Response
     *
     */
    public function getRecommend($product_id)
    {


        $category_id = DB::table('product_categories')->where('product_id', $product_id)->value('category_id');

        if (!empty($category_id)) {
            $product_ids = DB::table('product_categories')->where('category_id', $category_id)->where('product_id', '<>', $product_id)->select('product_id')->get()->toArray();
            if (!empty($product_ids)) {
                $product_ids =  json_decode(json_encode($product_ids), true);
                $product_ids = array_column($product_ids, 'product_id');
            } else {
                $product_ids = [];
            }


            $product_list = DB::table('product_flat')->whereIn('product_id', $product_ids)->select('name', 'price', 'product_id', 'url_key')->get()->toArray();

            if (!empty($product_list)) {

                $product_list =  json_decode(json_encode($product_list), true);

                foreach ($product_list as $key => $value) {
                    $product_list[$key]['image'] = DB::table('product_images')->where('product_id', $value['product_id'])->value('path');
                    $product_list[$key]['format_price'] = core()->currency($value['price']);

                    // add product review
                    $product_list[$key]['review'] = DB::table('product_reviews')->where('status', 'approved')->where('product_id', $value['product_id'])->avg('rating');
                    // add product review count
                    $product_list[$key]['review_count'] = DB::table('product_reviews')->where('status', 'approved')->where('product_id', $value['product_id'])->count();
                }


                return response()->json(['message' => 'Recommend found', 'code' => 200, 'data' => $product_list]);
            } else {
                return response()->json(['message' => 'Recommend not found', 'code' => 202, 'data' => []]);
            }
        } else {
            return response()->json(['message' => 'Recommend not found', 'code' => 202, 'data' => []]);
        }





        // $list = DB::table('cms_page_translations')->where('locale',$locale)->select('id','page_title','url_key','html_content','locale')->get()->toArray();


        // if($list){
        //     return response()->json(['message' => 'Cms found','code'=>200,'data'=>$list]);
        // }else{
        //     return response()->json(['message' => 'Cms not found','code'=>202,'data'=>[]]);
        // }

        //    return $list;
    }
}
