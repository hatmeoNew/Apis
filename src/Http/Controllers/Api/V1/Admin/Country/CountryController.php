<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country;

use Illuminate\Http\Request;
use Webkul\Core\Models\Country;
use Illuminate\Routing\Controller;

class CountryController extends Controller
{
    public function index()
    {
        return response()->json(Country::all());
    }

    public function show($id)
    {
        $country = Country::findOrFail($id);
        return response()->json($country);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|unique:countries,code',
            'name' => 'required',
        ]);

        $country = Country::create($validated);
        return response()->json($country, 201);
    }

    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $country->update($request->all());
        return response()->json($country);
    }

    public function destroy($id)
    {
        Country::destroy($id);
        return response()->json(['message' => 'Country deleted successfully.']);
    }
}
