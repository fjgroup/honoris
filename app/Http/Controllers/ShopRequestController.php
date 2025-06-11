<?php

namespace App\Http\Controllers;

use App\Models\ShopRequest;
use App\Models\City;
use App\Models\BuildingType;
use App\Http\Requests\StoreShopRequestRequest;
use App\Http\Requests\UpdateShopRequestRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Added for type hinting if needed, though Auth facade is used mostly

class ShopRequestController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $user = Auth::user();

        // Eager load relationships for display
        $relations = ['user:id,name', 'city:id,name', 'buildingType:id,name', 'assignedContract:id,contract_details']; // Example for assignedContract

        if ($user->role === 'admin' || $user->role === 'captain') {
            $this->authorize('viewAny', ShopRequest::class);
            $shopRequests = ShopRequest::with($relations)
                                      ->latest()
                                      ->paginate(10);
            return Inertia::render('Admin/ShopRequests/Index', ['shopRequests' => $shopRequests]);
        } else {
            // Regular users see only their own requests
            $shopRequests = ShopRequest::where('user_id', $user->id)
                                      ->with($relations)
                                      ->latest()
                                      ->paginate(10);
            return Inertia::render('User/ShopRequests/Index', ['shopRequests' => $shopRequests]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', ShopRequest::class);
        $cities = City::orderBy('name')->get(['id', 'name']);
        $buildingTypes = BuildingType::orderBy('name')->get(['id', 'name']);

        // Assuming a common create form, path might need adjustment based on actual FE structure
        return Inertia::render('User/ShopRequests/Create', [
            'cities' => $cities,
            'buildingTypes' => $buildingTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShopRequestRequest $request): RedirectResponse
    {
        $this->authorize('create', ShopRequest::class);
        $data = $request->validated();
        $data['user_id'] = Auth::id();
        $data['status'] = 'pending'; // Default status

        ShopRequest::create($data);

        // Redirect to user's list of their requests
        return redirect()->route('shop-requests.index')->with('success', 'Shop request submitted successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ShopRequest $shopRequest): InertiaResponse
    {
        $this->authorize('view', $shopRequest);
        $shopRequest->load(['user:id,name', 'city:id,name', 'buildingType:id,name', 'assignedContract']);

        $view = (Auth::user()->role === 'admin' || Auth::user()->role === 'captain')
                ? 'Admin/ShopRequests/Show'
                : 'User/ShopRequests/Show';

        return Inertia::render($view, ['shopRequest' => $shopRequest]);
    }

    /**
     * Show the form for editing the specified resource.
     * This is primarily for Admins/Captains to update status or assign contracts.
     */
    public function edit(ShopRequest $shopRequest): InertiaResponse
    {
        $this->authorize('update', $shopRequest); // Policy check for update action

        $shopRequest->load(['user:id,name', 'city:id,name', 'buildingType:id,name', 'assignedContract']);
        $cities = City::orderBy('name')->get(['id', 'name']);
        $buildingTypes = BuildingType::orderBy('name')->get(['id', 'name']);

        // Potentially load available ShopContracts if admin is assigning one
        // $availableContracts = ShopContract::where('status', 'available')->get();

        return Inertia::render('Admin/ShopRequests/Edit', [
            'shopRequest' => $shopRequest,
            'cities' => $cities, // If admin can change these
            'buildingTypes' => $buildingTypes, // If admin can change these
            // 'availableContracts' => $availableContracts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShopRequestRequest $request, ShopRequest $shopRequest): RedirectResponse
    {
        $this->authorize('update', $shopRequest);

        $shopRequest->update($request->validated());

        // Admins/Captains are redirected to the admin index view after updating.
        // If a user could somehow trigger an update (e.g., cancelling their own request),
        // the redirect logic might need to be more nuanced.
        return redirect()->route('admin.shop-requests.index')->with('success', 'Shop request updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShopRequest $shopRequest): RedirectResponse
    {
        $this->authorize('delete', $shopRequest);

        $shopRequest->delete();

        $redirectRoute = (Auth::user()->role === 'admin' || Auth::user()->role === 'captain')
                         ? 'admin.shop-requests.index'
                         : 'shop-requests.index';

        return redirect()->route($redirectRoute)->with('success', 'Shop request deleted successfully.');
    }
}
