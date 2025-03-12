<?php
namespace NexaMerchant\Apis\Http\Resources\Api\V1\Admin\Settings;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class ChannelCountryResource extends JsonResource
{

    public function toArray($request)
    {
        // get countries data

        $country = \DB::table('countries')->where('id', $this->country_id)->first();
        if(!$country) {
            return [];
        }

        return [
            'id'            => $this->id,
            'code'          => $country->code,
            'name'          => $country->name,
            'country_id'    => $this->country_id,
        ];
    }

}