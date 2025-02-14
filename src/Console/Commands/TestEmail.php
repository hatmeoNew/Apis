<?php
namespace NexaMerchant\Apis\Console\Commands;

use Illuminate\Support\Facades\Artisan;

use NexaMerchant\Apps\Console\Commands\CommandInterface;

class TestEmail extends CommandInterface {
    
    protected $signature = 'Apis:test-email';

    protected $description = 'Test email sending';

    public function getAppVer() {
        return config("Apis.ver");
    }

    public function getAppName() {
        return config("Apis.name");
    }

    public function handle()
    {
        // use klaviyo api to send email
        $this->info('Sending test email');
        $client = new \GuzzleHttp\Client();
        $header = [
            'Authorization' => 'Klaviyo-API-Key ' . config('mail.mailers.klaviyo.api_key'),
            'accept' => 'application/vnd.api+json',
            'content-type' => 'application/vnd.api+json',
            'revision' => '2025-01-15',
        ];

        $body = '{
    "data": {
        "type": "event",
        "attributes": {
            "properties": {
                "OrderId": "1234",
                "ProductID": "1111",
                "SKU": "WINNIEPOOH",
                "ProductName": "Winnie the Pooh",
                "Quantity": 1,
                "ProductURL": "http://www.example.com/path/to/product",
                "ImageURL": "http://www.example.com/path/to/product/image.png",
                "Categories": [
                    "Fiction",
                    "Children"
                ],
                "ProductBrand": "Kids Books"
            },
            "time": "2024-11-08T00:00:00",
            "value": 9.99,
            "value_currency": "USD",
            "unique_id": "' . uniqid() . '",
            "metric": {
                "data": {
                    "type": "metric",
                    "attributes": {
                        "name": "Ordered Product"
                    }
                }
            },
            "profile": {
                "data": {
                    "type": "profile",
                    "attributes": {
                        "email": "nice.lizhi@gmail.com",
                        "phone_number": "+15005550006"
                    }
                }
            }
        }
    }
}';

        

        $response = $client->request('POST', 'https://a.klaviyo.com/api/events', [
            'headers' => $header,
            'body' => $body
        ]);

        echo $response->getBody();

        $apiKey = config('mail.mailers.klaviyo.api_key');
        $url = 'https://a.klaviyo.com/api/track';

        $emailData = [
            'event' => 'Email Sent',
            'customer_properties' => [
                '$email' => 'nice.lizhi@gmail.com',
            ],
            'properties' => [
                'subject' => 'Test Email via Klaviyo',
                'body' => 'This is a test email sent using Klaviyo API',
            ],
        ];

        $response = $client->post($url, [
            'query' => ['api_key' => $apiKey],
            'json' => $emailData,
            'headers' => $header,
            'debug' => true,
        ]);

        var_dump($response->getBody()->getContents());

        $this->info('Response Code: ' . $response->getStatusCode());
        
    }
}