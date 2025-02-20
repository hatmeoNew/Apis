<?php

namespace NexaMerchant\Apis\Docs\V1\Admin\Models\Platform;

/**
 * @OA\Schema(
 *     title="Platform",
 *     description="Platform model",
 * )
 */
class Platform
{
    /**
     * @OA\Property(
     *     title="ID",
     *     description="ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="Name",
     *     description="Platform's Name",
     *     example="example",
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="Description",
     *     description="Platform's Description",
     *     example="example",
     * )
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property(
     *     title="Status",
     *     description="Platform's Status",
     *     example="active",
     * )
     *
     * @var string
     */
    private $status;

    /**
     * @OA\Property(
     *     title="URL",
     *     description="Platform's URL",
     *     example="https://example.com",
     * )
     *
     * @var string
     */
    private $url;

    /**
     * @OA\Property(
     *     title="IP",
     *     description="Platform's IP",
     *     example="1.1.1.1",
     * )
     * 
     * @var string
     */
    private $ip;

    /**
     * @OA\Property(
     *     title="Locale",
     *     description="Platform's Locale",
     *     example="en",
     * )
     *
     * @var string
     */
    private $locale;

    /**
     * @OA\Property(
     *     title="Currency",
     *     description="Platform's Currency",
     *     example="USD",
     * )
     *
     * @var string
     */
    private $currency;

    /**
     * @OA\Property(
     *     title="Created At",
     *     description="Platform's Created At",
     *     example="2021-01-01 00:00:00",
     * )
     *
     * @var string
     */
    private $created_at;

    /**
     * @OA\Property(
     *     title="Updated At",
     *     description="Platform's Updated At",
     *     example="2021-01-01 00:00:00",
     * )
     *
     * @var string
     */
    private $updated_at;
}