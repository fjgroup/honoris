<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingTypeRequest;
use App\Http\Requests\UpdateBuildingTypeRequest;
use App\Models\BuildingType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class BuildingTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', BuildingType::class);

        $buildingTypes = BuildingType::orderBy('name')->paginate(10);

        return Inertia::render('Admin/BuildingTypes/Index', [
            'buildingTypes' => $buildingTypes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', BuildingType::class);

        return Inertia::render('Admin/BuildingTypes/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBuildingTypeRequest $request): RedirectResponse
    {
        $this->authorize('create', BuildingType::class);

        $data = $request->validated();

        if ($request->hasFile('icon_path')) {
            $path = $request->file('icon_path')->store('public/building_icons');
            $data['icon_path'] = Storage::url($path);
        }

        BuildingType::create($data);

        return Redirect::route('admin.building-types.index')->with('success', 'Building type created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(BuildingType $buildingType): InertiaResponse
    {
        $this->authorize('view', $buildingType);

        return Inertia::render('Admin/BuildingTypes/Show', [
            'buildingType' => $buildingType,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BuildingType $buildingType): InertiaResponse
    {
        $this->authorize('update', $buildingType);

        return Inertia::render('Admin/BuildingTypes/Edit', [
            'buildingType' => $buildingType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBuildingTypeRequest $request, BuildingType $buildingType): RedirectResponse
    {
        $this->authorize('update', $buildingType);

        $data = $request->validated();

        if ($request->hasFile('icon_path')) {
            if ($buildingType->icon_path) {
                // Extract path relative to storage/app from the URL for deletion
                $oldPath = str_replace('/storage/', 'public/', $buildingType->icon_path);
                Storage::delete($oldPath);
            }
            $path = $request->file('icon_path')->store('public/building_icons');
            $data['icon_path'] = Storage::url($path);
        }

        $buildingType->update($data);

        return Redirect::route('admin.building-types.index')->with('success', 'Building type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BuildingType $buildingType): RedirectResponse
    {
        $this->authorize('delete', $buildingType);

        $buildingType->delete();

        return Redirect::route('admin.building-types.index')->with('success', 'Building type deleted successfully.');
    }
}
