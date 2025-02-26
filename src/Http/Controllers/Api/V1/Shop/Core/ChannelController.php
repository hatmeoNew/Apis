<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Shop\Core;

use Webkul\Core\Repositories\ChannelRepository;
use NexaMerchant\Apis\Http\Resources\Api\V1\Shop\Core\ChannelResource;
use Webkul\Core\Models\Channel;


class ChannelController extends CoreController
{
    /**
     * Is resource authorized.
     */
    public function isAuthorized(): bool
    {
        return false;
    }

    /**
     * Repository class name.
     */
    public function repository(): string
    {
        return ChannelRepository::class;
    }

    /**
     * Resource class name.
     */
    public function resource(): string
    {
        return ChannelResource::class;
    }

    /**
     * Get the Channel By URL.
     */
    public function getChannelByUrl($url){
        $channel = Channel::where('hostname', $url)->first();

        if (!$channel) {
            return response()->json([
                'message' => 'Channel not found',
                'status' => '404'
            ], 404);
        }
        return new ChannelResource($channel);
    }

    /**
     * 
     * Get the Channel Countries.
     * 
     * @param string $url
     * 
     */
    public function getChannelCountries($url){
        $channel = Channel::where('hostname', $url)->first();

        if (!$channel) {
            return response()->json([
                'message' => trans('admin::app.settings.channels.channel-not-found'),
                'status' => '404'
            ], 404);
        }

        $channelCountries = \NexaMerchant\Apis\Models\ChannelCountry::where('channel_id', $channel->id)->get();
        $countries = [];
        foreach ($channelCountries as $channelCountry) {
            $countries[] = $channelCountry->country_id;
        }

        // use contry_id to get the country name
        $countries = \Webkul\Core\Models\Country::whereIn('id', $countries)->get();

        return response()->json([
            'data' => $countries,
            'status' => '200'
        ], 200);
        
    }
}
