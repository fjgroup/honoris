<?php

namespace App\Http\Controllers;

use App\Models\MapPlot;
use App\Models\Map;
use App\Http\Requests\StoreMapPlotRequest;
use App\Http\Requests\UpdateMapPlotRequest;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse; // Though not used for JSON responses, good for consistency
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Redirect; // Though not used for JSON responses
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MapPlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse // Changed to JsonResponse
    {
        $this->authorize('viewAny', MapPlot::class);

        $mapId = $request->query('map_id');
        $plotsQuery = MapPlot::query();

        if ($mapId) {
            $plotsQuery->where('map_id', $mapId);
        }

        // Include map relationship to provide context for each plot
        $mapPlots = $plotsQuery->with('map:id,name')->orderBy('plot_identifier')->paginate(100); // Higher pagination for API

        // This endpoint is primarily for fetching plot data for a specific map.
        // For a table view, you might also pass all maps for a filter dropdown.
        // $maps = Map::orderBy('name')->get(['id', 'name']);
        // $selectedMap = $mapId ? Map::find($mapId, ['id', 'name']) : null;

        return response()->json(['map_plots' => $mapPlots]);
    }

    /**
     * Show the form for creating a new resource.
     * (Potentially for standalone CRUD, not primary for interactive map)
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', MapPlot::class);
        $maps = Map::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Admin/MapPlots/Create', ['maps' => $maps]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMapPlotRequest $request): RedirectResponse
    {
        $this->authorize('create', MapPlot::class);
        $data = $request->validated();
        // Ensure 'is_active' defaults to true if not provided or if it's not boolean from form
        $data['is_active'] = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);

        MapPlot::create($data);
        // $mapPlot->load('map:id,name'); // Not needed for redirect back
        return redirect()->back()->with('success', 'Map plot created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(MapPlot $mapPlot): JsonResponse
    {
        $this->authorize('view', $mapPlot);
        $mapPlot->load('map:id,name'); // Load map for context
        return response()->json($mapPlot);
    }

    /**
     * Show the form for editing the specified resource.
     * (Potentially for standalone CRUD, not primary for interactive map)
     */
    public function edit(MapPlot $mapPlot): InertiaResponse
    {
        $this->authorize('update', $mapPlot);
        $mapPlot->load('map:id,name');
        $maps = Map::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Admin/MapPlots/Edit', ['mapPlot' => $mapPlot, 'maps' => $maps]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMapPlotRequest $request, MapPlot $mapPlot): RedirectResponse
    {
        $this->authorize('update', $mapPlot);
        $data = $request->validated();

        if ($request->has('is_active')) {
            $data['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $mapPlot->update($data);
        // $mapPlot->load('map:id,name'); // Not needed for redirect back
        return redirect()->back()->with('success', 'Map plot updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MapPlot $mapPlot): RedirectResponse
    {
        $this->authorize('delete', $mapPlot);
        $mapPlot->delete();
        return redirect()->back()->with('success', 'Map plot deleted successfully.');
    }
}
