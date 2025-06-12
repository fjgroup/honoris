<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Ensure this is App\Http\Controllers\Controller
use App\Models\User;
use App\Http\Requests\Admin\StoreUserRequest; // Corrected namespace
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request; // Keep for Request type-hinting if needed for other methods

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): InertiaResponse
    {
        // $this->authorize('viewAny', User::class); // UserPolicy can be added later
        // Simplified authorization for now:
        if (! ($request->user()->role === 'admin' || $request->user()->role === 'captain')) {
            abort(403);
        }

        $users = User::orderBy('name')->paginate(10);
        return Inertia::render('Admin/Users/Index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): InertiaResponse
    {
        // $this->authorize('create', User::class); // UserPolicy can be added later
        if (! ($request->user()->role === 'admin' || $request->user()->role === 'captain')) {
            abort(403);
        }
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        // Authorization is handled by StoreUserRequest

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'rank' => $request->rank,
            'language_preference' => $request->language_preference,
            'email_verified_at' => now(), // Mark as verified since admin is creating
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    // Note: show(), edit(), update(), destroy() methods are not included as per
    // Route::resource(...)->only(['index', 'create', 'store']);
    // If they were needed, they would go here.
}
