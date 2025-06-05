# hatmeoNew/Apis

> NexaMerchant is a apps store for NexaMerchant Platform, NexaMerchant is a free merchant software,you can use it build for ecommence, blog, cms, erp etc

[![Build Status](https://github.com/hatmeoNew/Apis/workflows/Laravel/badge.svg)](https://github.com/hatmeoNew/apis)
[![Release](https://img.shields.io/github/release/hatmeoNew/apis.svg?style=flat-square)](https://github.com/hatmeoNew/apis/releases)
[![Latest Version on Packagist](https://img.shields.io/packagist/v/hatmeoNew/apis.svg?style=flat-square)](https://packagist.org/packages/hatmeoNew/apis)
[![Total Downloads](https://img.shields.io/packagist/dt/hatmeoNew/apis.svg?style=flat-square)](https://packagist.org/packages/hatmeoNew/apis)


# Requirements
 - PHP >= 8.1
 - Laravel >= 10
 - Sodium

# How to Install

```
NexaMerchant\Apis\Providers\ApisServiceProvider::class,
```
Add it to config/app.php $providers

# How to Publish

```
composer require hatmeo/apis
```

# How to Install
```
php artisan apis:install
```

# How to Generate API Docs
```
php artisan Apis:gendocs
```

# View Api Document
```
http://localhost/api/admin/documentation (Admin)
```
```
http://localhost/api/shop/documentation (Shop)
```

Bug Report
------------
[https://github.com/NexaMerchant/apis/issues](https://github.com/NexaMerchant/apis/issues)

# License
------------
`hatmeoNew Apis` is licensed under [The MIT License (MIT)](LICENSE.md).
