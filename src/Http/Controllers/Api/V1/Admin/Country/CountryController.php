<?php

namespace NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\Country;

use Illuminate\Http\Request;
use Webkul\Core\Models\Country;
use NexaMerchant\Apis\Http\Controllers\Api\V1\Admin\AdminController;

class CountryController extends AdminController
{
    public function index(Request $request)
    {
        $query = Country::query();

        if ($search = $request->input('code')) {
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%");
            });
        }

        if ($search = $request->input('name')) {
            $query->where(function ($q) use ($search) {
                $q->Where('name', 'like', "%{$search}%");
            });
        }

        $perPage = $request->input('per_page', 20); // 默认每页 20 条
        $countries = $query->paginate($perPage);

        return response()->json($countries);
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
