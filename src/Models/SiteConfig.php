<?php
namespace NexaMerchant\Apis\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiteConfig extends Model
{

    protected $table = 'site_config';

    protected $fillable = [
        'config',
    ];

    protected $casts = [
        'config' => 'json',
    ];


}