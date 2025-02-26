<?php
namespace NexaMerchant\Apis\Models;

use Illuminate\Database\Eloquent\Model;

class ChannelCountry extends Model {

    protected $table = 'channel_countries';

    protected $fillable = [
        'channel_id',
        'country_id',
    ];

    protected $casts = [
        'channel_id' => 'integer',
        'country_id' => 'integer',
    ];
}