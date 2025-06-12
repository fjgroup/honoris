<?php

namespace App\Http\Controllers;

use App\Models\ShopContract;
use App\Models\MapPlot;
use App\Models\Owner;
use App\Models\BuildingType;
use App\Models\User;
use App\Models\ShopRequest;
use App\Models\Map; // Added for type hinting
use App\Http\Requests\StoreShopContractRequest;
use App\Http\Requests\UpdateShopContractRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request; // For query params in create method
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse; // Added for API response

class ShopContractController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', ShopContract::class);
        $shopContracts = ShopContract::with([
            'mapPlot.map.city', // mapPlot > map > city
            'owner.user',       // owner > user (owner's linked user)
            'buildingType',
            'assignedToUser',   // user to whom the shop is assigned (if any)
            'lastUpdatedByUser' // user who last updated the contract
        ])->latest()->paginate(10);

        return Inertia::render('Admin/ShopContracts/Index', ['shopContracts' => $shopContracts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): InertiaResponse
    {
        $this->authorize('create', ShopContract::class);

        // Only plots that do not have an 'active' or 'pending_renewal' contract
        $mapPlots = MapPlot::whereDoesntHave('shopContract', function($query) {
            $query->whereIn('status', ['active', 'pending_renewal']);
        })->with('map.city')->get()->map(fn($mp) => [
            'id' => $mp->id,
            'identifier' => $mp->plot_identifier . ' (' . $mp->map->name . ', ' . $mp->map->city->name . ')',
        ]);

        $owners = Owner::with('user')->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name . ' (User: ' . $o->user->name . ')',
        ]);

        $buildingTypes = BuildingType::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']); // For 'assigned_to_user_id'

        $shopRequestId = $request->query('shop_request_id');
        $shopRequestDetails = null;
        if ($shopRequestId) {
            $shopRequestDetails = ShopRequest::with(['city', 'buildingType', 'user'])->find($shopRequestId);
        }

        return Inertia::render('Admin/ShopContracts/Create', [
            'mapPlots' => $mapPlots,
            'owners' => $owners,
            'buildingTypes' => $buildingTypes,
            'users' => $users,
            'shopRequestId' => $shopRequestId,
            'shopRequestDetails' => $shopRequestDetails, // Pass details to pre-fill form
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopContractRequest $request): RedirectResponse
    {
        $this->authorize('create', ShopContract::class);
        $data = $request->validated();
        $data['last_updated_by'] = Auth::id();

        $shopContract = ShopContract::create($data);

        if (!empty($data['shop_request_id'])) {
            ShopRequest::where('id', $data['shop_request_id'])
                       ->update(['status' => 'fulfilled', 'assigned_contract_id' => $shopContract->id]);
        }

        return redirect()->route('admin.shop-contracts.index')->with('success', 'Shop contract created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShopContract $shopContract): InertiaResponse
    {
        $this->authorize('view', $shopContract);
        $shopContract->load([
            'mapPlot.map.city',
            'owner.user',
            'buildingType',
            'assignedToUser',
            'lastUpdatedByUser',
            'shopRequest.user' // If it came from a shop request, load its user too
        ]);
        return Inertia::render('Admin/ShopContracts/Show', ['shopContract' => $shopContract]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShopContract $shopContract): InertiaResponse
    {
        $this->authorize('update', $shopContract);
        $shopContract->load(['mapPlot', 'owner', 'buildingType', 'assignedToUser']);

        // For editing, generally allow selecting the current plot.
        // More complex logic might be needed if trying to change to a plot that's unavailable.
        $mapPlots = MapPlot::with('map.city')->get()->map(fn($mp) => [
            'id' => $mp->id,
            'identifier' => $mp->plot_identifier . ' (' . $mp->map->name . ', ' . $mp->map->city->name . ')',
        ]);

        $owners = Owner::with('user')->get()->map(fn($o) => [
            'id' => $o->id,
            'name' => $o->name . ' (User: ' . $o->user->name . ')',
        ]);
        $buildingTypes = BuildingType::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/ShopContracts/Edit', [
            'shopContract' => $shopContract,
            'mapPlots' => $mapPlots,
            'owners' => $owners,
            'buildingTypes' => $buildingTypes,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopContractRequest $request, ShopContract $shopContract): RedirectResponse
    {
        $this->authorize('update', $shopContract);
        $data = $request->validated();
        $data['last_updated_by'] = Auth::id();

        $shopContract->update($data);

        // If status changed or shop_request_id changed, may need to update related ShopRequest
        if ($request->has('shop_request_id') || $request->has('status')) {
            if ($shopContract->shop_request_id && $data['status'] === 'active') {
                 ShopRequest::where('id', $shopContract->shop_request_id)
                       ->update(['status' => 'fulfilled', 'assigned_contract_id' => $shopContract->id]);
            }
            // Add more logic if other status changes on contract should reflect on request
        }


        return redirect()->route('admin.shop-contracts.index')->with('success', 'Shop contract updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShopContract $shopContract): RedirectResponse
    {
        $this->authorize('delete', $shopContract);

        $shopRequestId = $shopContract->shop_request_id;

        $shopContract->delete();

        // If the deleted contract was linked to a shop request, update the request
        if ($shopRequestId) {
            ShopRequest::where('id', $shopRequestId)
                       ->where('assigned_contract_id', $shopContract->id) // Ensure it was this contract
                       ->update(['status' => 'approved', 'assigned_contract_id' => null]); // Revert status, remove link
        }

        return redirect()->route('admin.shop-contracts.index')->with('success', 'Shop contract deleted successfully.');
    }

    /**
     * Get active contracts for a specific map, filtered by user visibility.
     */
    public function getActiveContractsForMap(Map $map): JsonResponse
    {
        // Policy check: Ensure user can at least view some contracts or map data.
        // Using 'viewAny' on ShopContract, but a more specific policy could be 'viewActiveContractsOnMap'.
        $this->authorize('viewAny', ShopContract::class);

        $userId = Auth::id();

        $contracts = ShopContract::where('map_id', $map->id)
            ->where('status', 'active') // Only active contracts
            ->with([
                // Eager load only necessary fields for display on the map
                'mapPlot:id,map_id,plot_identifier,coord_x,coord_y,width,height',
                'buildingType:id,name,icon_path',
                'owner:id,name,language_preference,is_for_all_users', // Include name for potential display
                // 'assignedToUser:id,name' // If needed to show who it's assigned to specifically on hover
            ])
            ->get()
            ->filter(function ($contract) use ($userId) {
                // Filter for visibility:
                // 1. If the contract's owner has 'is_for_all_users' set to true.
                // 2. OR if the contract is specifically assigned to the current viewing user.
                if ($contract->owner && $contract->owner->is_for_all_users) {
                    return true;
                }
                // Also include if the user themselves is the owner, even if not 'is_for_all_users'
                // This depends on how `owner_id` on ShopContract relates to `User` model.
                // Assuming Owner model has a user_id field for the owner.
                if ($contract->owner && $contract->owner->user_id === $userId) {
                    return true;
                }

                return $contract->assigned_to_user_id === $userId;
            });

        return response()->json($contracts->values()); // values() to re-index array after filter
    }
}
