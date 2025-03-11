<?php
namespace NexaMerchant\Apis\Enum;

use BenSampo\Enum\Enum;

class ApiCacheKey extends Enum
{
    const ORDER = 'order';
    const PRODUCT = 'product';
    const USER = 'user';

    // cart rules
    const CART_RULES = 'cart_rules';
    const CART_RULES_PRODUCT = 'cart_rules_product';
    const CART_RULES_CATEGORY = 'cart_rules_category';

    // cache tags
    const API_SHOP = 'api_shop';
    const API_ADMIN = 'api_admin';
    const API_CACHE = 'api_cache';
}