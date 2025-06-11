<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\Request; // Added
use Illuminate\Http\JsonResponse; // Added

class CityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse|JsonResponse // Modified return type
    {
        $this->authorize('viewAny', City::class);

        if ($request->query('all') === 'true') {
            $cities = City::orderBy('name')->get();
            return response()->json($cities);
        }

        $cities = City::orderBy('name')->paginate(10);

        return Inertia::render('Admin/Cities/Index', [
            'cities' => $cities,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', City::class);

        return Inertia::render('Admin/Cities/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCityRequest $request): RedirectResponse
    {
        $this->authorize('create', City::class);

        City::create($request->validated());

        return Redirect::route('admin.cities.index')->with('success', 'City created successfully.');
    }

    /**
     * Display the specified resource.
     * Note: Basic resource controllers often don't use 'show' if 'edit' serves display purposes.
     * If a separate show view is needed, this can be implemented.
     * For now, we'll assume 'edit' is sufficient or 'show' is not required for this CRUD.
     */
    public function show(City $city): InertiaResponse
    {
        $this->authorize('view', $city);
        // This typically would render an Admin/Cities/Show component
        // For this task, we'll redirect to edit or assume it's not the primary focus.
        // return Inertia::render('Admin/Cities/Edit', ['city' => $city]);
        // Or, if you have a show page:
        return Inertia::render('Admin/Cities/Show', ['city' => $city]);
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(City $city): InertiaResponse
    {
        $this->authorize('update', $city);

        return Inertia::render('Admin/Cities/Edit', [
            'city' => $city,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $this->authorize('update', $city);

        $city->update($request->validated());

        return Redirect::route('admin.cities.index')->with('success', 'City updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(City $city): RedirectResponse
    {
        $this->authorize('delete', $city);

        $city->delete();

        return Redirect::route('admin.cities.index')->with('success', 'City deleted successfully.');
    }
}
