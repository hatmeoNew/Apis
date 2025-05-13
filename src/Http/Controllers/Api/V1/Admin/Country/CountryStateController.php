<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country;

use Webkul\Core\Models\CountryState;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CountryStateController extends Controller
{
    public function index(Request $request)
    {
        $query = CountryState::query();

        if ($search = $request->input('code')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        if ($search = $request->input('default_name')) {
            $query->where(function ($q) use ($search) {
                $q->where('default_name', 'like', "%{$search}%");
            });
        }

        if ($search = $request->input('country_code')) {
            $query->where(function ($q) use ($search) {
                $q->where('country_code', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20); // 默认每页 20 条
        $states = $query->paginate($perPage);

        return response()->json($states);
    }

    public function show($id)
    {
        return response()->json(CountryState::findOrFail($id));
    }

    public function getByCountryCode(Request $request, $countryCode)
    {
        $query = CountryState::where('country_code', $countryCode);

        if ($request->input('code')) {
            $search = $request->input('code');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        if ($request->input('default_name')) {
            $search = $request->input('default_name');
            $query->where(function ($q) use ($search) {
                $q->where('default_name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20);
        $states = $query->paginate($perPage);

        return response()->json($states);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|exists:countries,code',
            'code' => 'required|string',
            'default_name' => 'required|string',
        ]);

        $state = CountryState::create($validated);
        return response()->json($state, 200);
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
