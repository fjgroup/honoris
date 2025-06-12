<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\City;
use App\Http\Requests\StoreMapRequest;
use App\Http\Requests\UpdateMapRequest;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response as InertiaResponse; // Alias to avoid conflict if Response facade is used directly
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request; // Added
use Illuminate\Http\JsonResponse; // Added

class MapController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse|JsonResponse // Modified
    {
        $this->authorize('viewAny', Map::class);

        if ($request->has('city_id')) {
            $cityId = $request->query('city_id');
            // Return only 'id' and 'name' for dropdowns, include 'image_path' if needed for previews
            $maps = Map::where('city_id', $cityId)
                       ->orderBy('name')
                       ->select('id', 'name', 'image_path') // Select specific fields
                       ->get();
            return response()->json($maps);
        }

        $maps = Map::with('city')->orderBy('name')->paginate(10);
        return Inertia::render('Admin/Maps/Index', ['maps' => $maps]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', Map::class);
        $cities = City::orderBy('name')->get();
        return Inertia::render('Admin/Maps/Create', ['cities' => $cities]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMapRequest $request): RedirectResponse
    {
        $this->authorize('create', Map::class);
        $data = $request->validated();
        if ($request->hasFile('image_path')) {
            $path = $request->file('image_path')->store('public/map_images');
            $data['image_path'] = Storage::url($path);
        }
        Map::create($data);
        return Redirect::route('admin.maps.index')->with('success', 'Map created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Map $map): InertiaResponse // Changed from JsonResponse
    {
        $this->authorize('view', $map);
        $map->load('city:id,name'); // Load only necessary city fields
        return Inertia::render('Admin/Maps/Show', [
            'map' => $map,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Map $map): InertiaResponse
    {
        $this->authorize('update', $map);
        $map->load('city'); // Ensure city is loaded
        $cities = City::orderBy('name')->get();
        return Inertia::render('Admin/Maps/Edit', ['map' => $map, 'cities' => $cities]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMapRequest $request, Map $map): RedirectResponse
    {
        $this->authorize('update', $map);
        $data = $request->validated();

        if ($request->hasFile('image_path')) {
            if ($map->image_path) {
                $oldPath = str_replace('/storage/', 'public/', $map->image_path);
                Storage::delete($oldPath);
            }
            $path = $request->file('image_path')->store('public/map_images');
            $data['image_path'] = Storage::url($path);
        } else if (array_key_exists('image_path', $data) && $data['image_path'] === null) {
            // This handles explicit null submission to remove the image
            if ($map->image_path) {
                $oldPath = str_replace('/storage/', 'public/', $map->image_path);
                Storage::delete($oldPath);
            }
            $data['image_path'] = null;
        }
        // If 'image_path' is not in $data and no file uploaded, existing image_path remains.

        $map->update($data);
        return Redirect::route('admin.maps.index')->with('success', 'Map updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Map $map): RedirectResponse
    {
        $this->authorize('delete', $map);
        if ($map->image_path) {
            $oldPath = str_replace('/storage/', 'public/', $map->image_path);
            Storage::delete($oldPath);
        }
        $map->delete();
        return Redirect::route('admin.maps.index')->with('success', 'Map deleted.');
    }
}
