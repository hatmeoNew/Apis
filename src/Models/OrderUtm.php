<?php
namespace NexaMerchant\Apis\Models;

use Illuminate\Database\Eloquent\Model;

class OrderUtm extends Model {

    protected $table = 'order_utm';

    protected $fillable = [
        'order_id',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'gclid',
        'yclid',
        'msclkid',
        'fbclid',
        'dclid',
        'mcid',
        'gclsrc',
        'utmcsr',
        'utmccn',
    ];

    protected $casts = [
        'order_id' => 'integer',
        'utm_source' => 'string',
        'utm_medium' => 'string',
        'utm_campaign' => 'string',
        'utm_term' => 'string',
        'utm_content' => 'string',
        'gclid' => 'string',
        'yclid' => 'string',
        'msclkid' => 'string',
        'fbclid' => 'string',
        'dclid' => 'string',
        'mcid' => 'string',
        'gclsrc' => 'string',
        'utmcsr' => 'string',
        'utmccn' => 'string',
    ];
}