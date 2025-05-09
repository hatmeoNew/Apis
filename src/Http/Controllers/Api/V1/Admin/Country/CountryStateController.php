<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country;

use Webkul\Core\Models\CountryState;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CountryStateController extends Controller
{
    public function index()
    {
        return response()->json(CountryState::all());
    }

    public function show($id)
    {
        return response()->json(CountryState::findOrFail($id));
    }

    public function getByCountryCode($countryCode)
    {
        $states = CountryState::where('country_code', $countryCode)->get();

        if ($states->isEmpty()) {
            return response()->json(['message' => 'No states found for this country.'], 404);
        }

        return response()->json($states);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_code' => 'required|exists:countries,code',
            'default_name' => 'required|string',
        ]);

        $state = CountryState::create($validated);
        return response()->json($state, 201);
    }

    public function update(Request $request, $id)
    {
        $state = CountryState::findOrFail($id);
        $state->update($request->all());
        return response()->json($state);
    }

    public function destroy($id)
    {
        CountryState::destroy($id);
        return response()->json(['message' => 'State deleted successfully.']);
    }
}
