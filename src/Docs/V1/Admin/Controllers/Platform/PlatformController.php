<?php

namespace Apps\Apis\Docs\V1\Admin\Controllers\Platform;

/**
 * @group Admin/Platform
 *
 * @authenticated
 *
 * APIs for managing platform
 */
class PlatformController
{
    /**@OA\Get(
     *     path="/admin/platform",
     *     tags={"Admin/Platform"},
     *     summary="Get all platforms",
     *     description="Get all platforms",
     *     operationId="getPlatforms",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Platform")
     *         )
     *     )
     * )
     */
    public function index(){
        
    }
    public function create(){

    }
}