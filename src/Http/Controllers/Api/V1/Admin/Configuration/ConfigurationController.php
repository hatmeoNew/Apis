<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Configuration;

use Nicelizhi\Manage\Http\Requests\ConfigurationForm;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Core\Tree;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;

class ConfigurationController extends AdminController
{
    /**
     * Tree instance.
     *
     * @var \Webkul\Core\Tree
     */
    protected $configTree;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(protected CoreConfigRepository $coreConfigRepository)
    {
        parent::__construct();

        $this->prepareConfigTree();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $items = $this->configTree->items;

        // 递归查找并获取 value_key 对应的数据
        $applyValueKey = function (&$nodes) use (&$applyValueKey) {
            foreach ($nodes as &$node) {
                if (isset($node['fields']) && is_array($node['fields'])) {
                    foreach ($node['fields'] as &$field) {
                        if (isset($field['value_key'])) {
                            $field['value'] = core()->getConfigData($field['value_key']);
                        }
                    }
                }
                if (isset($node['children']) && !empty($node['children'])) {
                    $applyValueKey($node['children']);
                }
            }
        };

        $applyValueKey($items);

        return response([
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(ConfigurationForm $request)
    {

        $items = $request->all();

        $locale = $request->input('locale');
        $channel = $request->input('channel');

        $items = $request->input("items");

        foreach ($items as $key => $item) {
            //var_dump($key, $item);exit;
            // check the code is exists or not
            $config = $this->coreConfigRepository->findOneWhere([
                'code'    => $key,
                'channel_code' => $channel,
            ]);
            //var_dump($config);exit;
            if ($config) {
                $config->update([
                    'value' => $item,
                ]);
                //core()->saveConfig($key, $item['value'], $channel, $locale);
                continue;
            }

            // $this->coreConfigRepository->create([
            //     'code'    => $key,
            //     'value'   => $item['value'],
            //     'locale'  => $locale,
            //     'channel' => $channel,
            // ]);
        }

        return response([
            'message' => trans('Apis::app.admin.configuration.save-success')
        ]);
    

        //$coreConfigData = $this->coreConfigRepository->create($request->except(['_token', 'admin_locale']));

        return response([
            'data'    => $coreConfigData,
            'message' => trans('Apis::app.admin.configuration.update-success'),
        ]);
    }

    /**
     * Prepares config tree.
     *
     * @return void
     */
    private function prepareConfigTree()
    {
        $tree = Tree::create();
        foreach (config('core') as $item) {
            $tree->add($item);
        }

        $tree->items = core()->sortItems($tree->items);

        $this->configTree = $tree;
    }

    public function get_value_keys($data) {
        $value_keys = [];
    
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if ($key === 'value_key') {
                    $value_keys[] = $value;
                } elseif (is_array($value)) {
                    $value_keys = array_merge($value_keys, $this->get_value_keys($value));
                }
            }
        }
    
        return $value_keys;
    }
    
    //$values = get_value_keys($data);
}
