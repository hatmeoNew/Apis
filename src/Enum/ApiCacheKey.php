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
    const API_SHOP_PRODUCTS = 'api_shop_products';
    const API_SHOP_CATEGORY = 'api_shop_category';
    const API_SHOP_TEMPLATE = 'api_shop_template';
    const API_SHOP_CHANNEL = 'api_shop_channel';
    const API_SHOP_CONFIG = 'api_shop_config';
    const API_SHOP_CMS = 'api_shop_cms';


    const API_ADMIN_PRODUCTS = 'api_admin_products';
    const API_ADMIN_CATEGORIES = 'api_admin_categories';
    const API_ADMIN_ORDER = 'api_admin_order';
}