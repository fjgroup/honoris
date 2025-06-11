<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\User;
use App\Http\Requests\StoreOwnerRequest;
use App\Http\Requests\UpdateOwnerRequest;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response as InertiaResponse;
use Illuminate\Support\Facades\Redirect;

class OwnerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): InertiaResponse
    {
        $this->authorize('viewAny', Owner::class);
        $owners = Owner::with('user')->orderBy('name')->paginate(10);
        return Inertia::render('Admin/Owners/Index', ['owners' => $owners]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): InertiaResponse
    {
        $this->authorize('create', Owner::class);
        // Get users who are not already owners.
        // Further filtering (e.g., by a specific role) could be added if necessary.
        $users = User::whereDoesntHave('owner')->orderBy('name')->get();
        return Inertia::render('Admin/Owners/Create', ['users' => $users]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOwnerRequest $request): RedirectResponse
    {
        $this->authorize('create', Owner::class);
        $data = $request->validated();
        Owner::create($data);
        return Redirect::route('admin.owners.index')->with('success', 'Owner profile created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Owner $owner): InertiaResponse
    {
        $this->authorize('view', $owner);
        $owner->load('user');
        return Inertia::render('Admin/Owners/Show', ['owner' => $owner]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Owner $owner): InertiaResponse
    {
        $this->authorize('update', $owner);
        $owner->load('user');
        $linkedUserId = $owner->user_id;
        // Users for dropdown: current user of this owner profile + users not yet linked to any owner profile.
        $users = User::whereDoesntHave('owner')
                     ->orWhere('id', $linkedUserId)
                     ->orderBy('name')
                     ->get();
        return Inertia::render('Admin/Owners/Edit', ['owner' => $owner, 'users' => $users]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateOwnerRequest $request, Owner $owner): RedirectResponse
    {
        $this->authorize('update', $owner);
        $data = $request->validated();
        $owner->update($data);
        return Redirect::route('admin.owners.index')->with('success', 'Owner profile updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Owner $owner): RedirectResponse
    {
        $this->authorize('delete', $owner);
        // Future: Consider implications for related entities like ShopContracts.
        // For now, direct deletion.
        $owner->delete();
        return Redirect::route('admin.owners.index')->with('success', 'Owner profile deleted.');
    }
}
