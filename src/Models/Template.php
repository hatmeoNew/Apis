<?php
namespace NexaMerchant\Apis\Models;

class Template extends Model
{
    protected $table = 'template';

    protected $fillable = [
        'name',
        'des',
        'template_countent',
    ];

    protected $casts = [
        'template_countent' => 'json',
    ];
}