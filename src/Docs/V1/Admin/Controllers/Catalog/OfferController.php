<?php
namespace NexaMerchant\Apis\Docs\V1\Admin\Controllers\Catalog;

class OfferController {
    
    /**
     * @OA\Get(
     *     path="/api/v1/admin/catalog/offers/clear-cache/{slug}",
     *     tags={"Admin Catalog Offers clear cache"},
     *     summary="Clear cache",
     *     description="Clear cache",
     *     operationId="clearCache",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Cache cleared successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function clearCache(){}

    /**
     * @OA\Post(
     *     path="/api/v1/admin/catalog/offers/sell-point/{slug}",
     *     tags={"Admin Catalog Offers sell point"},
     *    summary="Sell point",
     *    description="Sell point",
     *   operationId="sellPoint",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All offers",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function sellPoint(){}

    
    
}