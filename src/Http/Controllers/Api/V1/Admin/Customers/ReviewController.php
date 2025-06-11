<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Customers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Nicelizhi\Manage\Http\Requests\MassDestroyRequest;
use Nicelizhi\Manage\Http\Requests\MassUpdateRequest;
use Webkul\Product\Repositories\ProductReviewRepository;
use NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Catalog\ProductReviewResource;
use NexaMerchant\Apis\Imports\ProductReviewImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cache;
use NexaMerchant\Apis\Enum\ApiCacheKey;
use Webkul\Product\Models\Product;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Webkul\Product\Models\ProductReview;

class ReviewController extends BaseController
{
    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return ProductReviewRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ProductReviewResource::class;
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(int $id)
    {
        $this->validate(request(), [
            'status' => 'in:approved,disapproved,pending',
            "rating" => "numeric|min:1|max:5",
            "sort" => "numeric|min:1|max:100",
        ]);

        Event::dispatch('customer.review.update.before', $id);

        $review = $this->getRepositoryInstance()->update(request()->only(['status','sort','rating']), $id);

        Event::dispatch('customer.review.update.after', $review);

        Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

        return response([
            'data'    => new ProductReviewResource($review),
            'message' => trans('Apis::app.admin.customers.reviews.update-success'),
        ]);
    }

    /**
     * Delete the review.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        Event::dispatch('customer.review.delete.before', $id);

        $this->getRepositoryInstance()->delete($id);

        Event::dispatch('customer.review.delete.after', $id);

        Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

        return response([
            'message' => trans('Apis::app.admin.customers.reviews.delete-success'),
        ]);
    }

    /**
     * Mass approve the reviews.
     *
     * @return \Illuminate\Http\Response
     */
    public function massUpdate(MassUpdateRequest $massUpdateRequest)
    {
        $indices = $massUpdateRequest->input('indices');

        foreach ($indices as $id) {
            Event::dispatch('customer.review.update.before', $id);

            $review = $this->getRepositoryInstance()->update([
                'status' => $massUpdateRequest->input('value'),
            ], $id);

            Event::dispatch('customer.review.update.after', $review);
        }

        Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

        return response([
            'message' => trans('Apis::app.admin.customers.reviews.mass-operations.update-success'),
        ]);
    }

    /**
     * Mass delete the reviews on the products.
     *
     * @return \Illuminate\Http\Response
     */
    public function massDestroy(MassDestroyRequest $massDestroyRequest)
    {
        $indices = $massDestroyRequest->input('indices');

        foreach ($indices as $index) {
            Event::dispatch('customer.review.delete.before', $index);

            $this->getRepositoryInstance()->delete($index);

            Event::dispatch('customer.review.delete.after', $index);
        }

        Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

        return response([
            'message' => trans('Apis::app.admin.customers.reviews.mass-operations.delete-success'),
        ]);
    }

    /**
     *
     * product id import a xls file
     *
     * upload a xls file to import its products review
     */
    public function import(Request $request) {

        $this->validate(request(), [
            'file' => 'required|mimes:xls,xlsx',
        ]);

        try {

            // 读取 Excel 文件
            $data = Excel::toArray([], $request->file('file'));
            $errors = [];

            // 遍历每一行数据进行校验
            foreach ($data[0] as $rowIndex => $row) {
                // 跳过表头
                if ($rowIndex === 0) {
                    continue;
                }

                // 如果$row全部为空，则跳过该行
                if (empty($row[0]) && empty($row[1]) && empty($row[2]) && empty($row[3]) && empty($row[4]) && empty($row[5]) && empty($row[6])) {
                    continue;
                }

                if (empty($row[0])) {
                    $errors[$rowIndex + 1] = 'Product ID is required.';
                } else {
                    if (!Product::where('id', $row[0])->exists()) {
                        $errors[$rowIndex + 1] = 'Product ID ' . $row[0] . ' does not exist.';
                    }
                }

                if (empty($row[2])) {
                    $errors[$rowIndex + 1] = 'Customer email is required.';
                }

            }

            // 如果有错误，返回错误信息
            if (!empty($errors)) {
                return response()->json([
                    'message' => '数据存在不符合要求的行',
                    'errors' => $errors
                ], 422);
            }

            Excel::import(new ProductReviewImport, $request->file('file'));

            //clear cache by product id
            Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

            return response()->json([
                'message' => 'Product reviews imported successfully.',
            ]);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();

            return response()->json([
                'message'  => 'Import failed due to validation errors.',
                'errors'   => $failures,
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during import.',
                'error'   => $e->getMessage(),
            ], 500);
        }

    }

    /**
     * @description: batch Add customers
     * @param {Request} $request
     * @return {*}
     * @Author: rickon
     * @Date: 2025-06-11 14:23:14
     */
    public function batchAdd(Request $request)
    {
        try {
            $data = $request->all();
            $errors = [];
            foreach ($data as $rowIndex => $row) {
                // 验证数据
                $validator = Validator::make($row, [
                    // product_id是否存在
                    'product_id'     => 'required|integer|exists:products,id',
                    'customer_name'  => 'required|string',
                    'customer_email' => 'required|email',
                    'title'          => 'required|string',
                    'rating'         => 'required|integer|min:1|max:5',
                    'comment'        => 'nullable|string',
                    'images'         => 'nullable|array',
                ]);
                if ($validator->fails()) {
                    $errors[$rowIndex] = $validator->errors();
                }
            }

            if (count($errors) > 0) {
                return response()->json([
                    'status'  => false,
                    'errors'  => $errors,
                ], 422);
            }

            foreach ($data as $rowIndex => $row) {
                $product = Product::where('id', $row['product_id'])->first();
                if (empty($product)) {
                    continue;
                }

                $customer = DB::table('customers')->where('email', $row['customer_email'])->first();
                if(!$customer) {
                    // create a new customer
                    DB::table('customers')->insertGetId([
                        'first_name'  => $row['customer_name'],
                        'last_name'   => $row['customer_name'],
                        'email'       => $row['customer_email'],
                        'password'    => bcrypt('password'),
                        'is_verified' => 1,
                        'created_at'  => now(),
                        'updated_at'  => now(),
                    ]);

                    $customer = DB::table('customers')->where('email', $row['customer_email'])->first();
                }

                $productReview = new ProductReview([
                    'product_id'  => $product->id,
                    'customer_id' => $customer->id,
                    'name'        => $row['customer_name'],
                    'title'       => $row['title'],
                    'rating'      => $row['rating'],
                    'comment'     => $row['comment'] ?? '',
                    'sort'        => isset($row['sort']) ? $row['sort'] : 0,
                    'status'      => 'pending',
                    'sort'        => 0,
                ]);

                $productReview->save();

                if (!empty($row['images'])) {
                    foreach ($row['images'] as $image) {
                        $productReview->images()->create([
                            'path' => $image,
                            'type' => 'image',
                            'mime_type' => 'jpeg',
                        ]);
                    }
                }
            }

            Cache::tags(ApiCacheKey::API_SHOP_PRODUCTS_COMMENTS)->flush();

            return response()->json([
                'message' => 'Product reviews batch Add successfully.',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred during import.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
