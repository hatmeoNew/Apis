<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Catalog;

use Illuminate\Support\Facades\Event;
use Nicelizhi\Manage\Http\Requests\CategoryRequest;
use Nicelizhi\Manage\Http\Requests\MassDestroyRequest;
use Nicelizhi\Manage\Http\Requests\MassUpdateRequest;
use Nicelizhi\Manage\Http\Requests\CategoryProductAttachRequest;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Models\Channel;
use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Catalog\CategoryResource;
use Illuminate\Support\Facades\Cache;
use NexaMerchant\Apis\Enum\ApiCacheKey;

class CategoryController extends CatalogController
{
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
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        Event::dispatch('catalog.category.create.before');

        $category = $this->getRepositoryInstance()->create($request->only([
            'name',
            'parent_id',
            'description',
            'slug',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'status',
            'position',
            'display_mode',
            'attributes',
            'logo_path',
            'banner_path',
        ]));

        Event::dispatch('catalog.category.create.after', $category);

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'data'    => new CategoryResource($category),
            'message' => trans('Apis::app.admin.catalog.categories.create-success'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, int $id)
    {
        $this->getRepositoryInstance()->findOrFail($id);

        Event::dispatch('catalog.category.update.before', $id);


          $logo_path =  $request->input('logo_path');
        //   $banner_path =  $request->input('banner_path');


        // var_dump($request->all());

        $request['logo_path'] = $logo_path[0];
        // $request['logo_path'] = $logo_path[0];



        $category = $this->getRepositoryInstance()->update($request->only([
            'name',
            'parent_id',
            'description',
            'slug',
            'meta_title',
            'meta_keywords',
            'meta_description',
            'status',
            'position',
            'display_mode',
            'attributes',
            'logo_path',
            'banner_path',
            core()->getCurrentLocale()->code
        ]), $id);




        Event::dispatch('catalog.category.update.after', $category);

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_SHOP_CMS])->flush();
        Cache::tags([ApiCacheKey::API_SHOP])->flush();

        return response([
            'data'    => new CategoryResource($category),
            'message' => trans('Apis::app.admin.catalog.categories.update-success'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        $category = $this->getRepositoryInstance()->findOrFail($id);

        if (! $this->isCategoryDeletable($category)) {
            return response([
                'message' => trans('Apis::app.admin.catalog.categories.root-category-delete'),
            ], 400);
        }

        Event::dispatch('catalog.category.delete.before', $id);

        $this->getRepositoryInstance()->delete($id);

        Event::dispatch('catalog.category.delete.after', $id);

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'message' => trans('Apis::app.admin.catalog.categories.delete-success'),
        ]);
    }

    /**
     * Mass update Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest)
    {
        $indices = $massUpdateRequest->input('indices');

        foreach ($indices as $categoryId) {
            $this->getRepositoryInstance()->findOrFail($categoryId);

            Event::dispatch('catalog.categories.mass-update.before', $categoryId);

            $category = $this->getRepositoryInstance()->find($categoryId);

            $category->status = $massUpdateRequest->input('value');

            $category->save();

            Event::dispatch('catalog.categories.mass-update.after', $category);
        }

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'message' => trans('Apis::app.admin.catalog.categories.mass-operations.update-success'),
        ]);
    }

    /**
     * Remove the specified resources from database.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest)
    {
        $categories = $this->getRepositoryInstance()->findWhereIn('id', $massDestroyRequest->indices);

        if ($this->containsNonDeletableCategory($categories)) {
            return response([
                'message' => trans('Apis::app.admin.catalog.categories.error.root-category-delete'),
            ], 400);
        }

        $categories->each(function ($category) {
            Event::dispatch('catalog.category.delete.before', $category->id);

            $this->getRepositoryInstance()->delete($category->id);

            Event::dispatch('catalog.category.delete.after', $category->id);
        });

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'message' => trans('Apis::app.admin.catalog.categories.mass-operations.delete-success'),
        ]);
    }

    /**
     * Check whether the current category is deletable or not.
     *
     * This method will fetch all root category ids from the channel. If `id` is present,
     * then it is not deletable.
     *
     * @param  \Webkul\Category\Models\Category  $category
     */
    private function isCategoryDeletable($category): bool
    {
        if ($category->id === 1) {
            return false;
        }

        return ! Channel::pluck('root_category_id')->contains($category->id);
    }

    /**
     * Check whether indexes contains non deletable category or not.
     *
     * @param  \Kalnoy\Nestedset\Collection  $categoryIds
     * @return bool
     */
    private function containsNonDeletableCategory($categories)
    {
        return $categories->contains(fn ($category) => ! $this->isCategoryDeletable($category));
    }

    /**
     * Add products to the category.
     *
     * @return \Illuminate\Http\Response
     */
    public function addProducts(CategoryProductAttachRequest $request)
    {
        $category = $this->getRepositoryInstance()->findOrFail(
            $request->input('category_id')
        );

        $category->products()->syncWithoutDetaching($request->input('product_ids'));

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'data'    => new CategoryResource($category),
            'message' => trans('Apis::app.admin.catalog.categories.add-products-success'),
        ]);
    }

    /**
     * Remove products from the category.
     *
     * @return \Illuminate\Http\Response
     */
    public function removeProducts(CategoryProductAttachRequest $request)
    {
        $category = $this->getRepositoryInstance()->findOrFail(
            $request->input('category_id')
        );

        $category->products()->detach($request->input('product_ids'));

        // clear cache
        Cache::tags([ApiCacheKey::API_SHOP_CATEGORY])->flush();
        Cache::tags([ApiCacheKey::API_ADMIN_CATEGORY])->flush();

        return response([
            'data'    => new CategoryResource($category),
            'message' => trans('Apis::app.admin.catalog.categories.remove-products-success'),
        ]);
    }
}
