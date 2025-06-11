<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Import Models and Policies
use App\Models\City;
use App\Policies\CityPolicy;
use App\Models\BuildingType;
use App\Policies\BuildingTypePolicy;
use App\Models\Map;
use App\Policies\MapPolicy;
use App\Models\MapPlot;
use App\Policies\MapPlotPolicy;
use App\Models\Owner;
use App\Policies\OwnerPolicy;
use App\Models\ShopRequest;
use App\Policies\ShopRequestPolicy;
use App\Models\ShopContract;
use App\Policies\ShopContractPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy', // Default comment
        City::class => CityPolicy::class,
        BuildingType::class => BuildingTypePolicy::class,
        Map::class => MapPolicy::class,
        MapPlot::class => MapPlotPolicy::class,
        Owner::class => OwnerPolicy::class,
        ShopRequest::class => ShopRequestPolicy::class,
        ShopContract::class => ShopContractPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
