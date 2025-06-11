<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ShopContract;
use App\Models\ShopRequest; // Added for updating related ShopRequest
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ManageShopContracts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:manage-lifecycle';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage shop contract statuses based on expiry and renewal cycle (e.g., set to pending_renewal, then to expired).';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::today();
        $this->info("Running Shop Contract Lifecycle Management for {$today->toDateString()}...");
        Log::info("Running Shop Contract Lifecycle Management for {$today->toDateString()}...");

        $pendingRenewalCount = 0;
        // --- Part 1: Set contracts to 'pending_renewal' ---
        // This logic assumes contracts end on the 3rd of the month.
        // If today is the 3rd, any 'active' contract ending today should be moved to 'pending_renewal'.
        // This gives admins a window (e.g., until the 12th) to renew.
        if ($today->day == 3) {
            $contractsToEndToday = ShopContract::where('status', 'active')
                                             ->whereDate('end_date', $today->toDateString())
                                             ->get();
            foreach ($contractsToEndToday as $contract) {
                $contract->status = 'pending_renewal';
                $contract->last_updated_by = null; // Or a system user ID if available
                $contract->save();
                Log::info("Contract ID: {$contract->id} status set to pending_renewal as its end_date is today ({$today->toDateString()}).");
                $pendingRenewalCount++;
            }
            $this->info($pendingRenewalCount . ' active contracts ending today set to pending_renewal.');
        } else {
            $this->info('Not the 3rd of the month, skipping pending_renewal step for contracts ending today.');
        }

        // --- Part 2: Set 'pending_renewal' or overdue 'active' contracts to 'expired' ---
        // This runs daily. It will catch:
        // 1. 'pending_renewal' contracts whose end_date has passed (meaning they weren't renewed in time).
        // 2. 'active' contracts whose end_date has passed (e.g., if the pending_renewal step was missed or contract had a very short term).
        $expiredContractsCount = 0;
        $contractsPastEndDate = ShopContract::whereIn('status', ['active', 'pending_renewal'])
                                           ->whereDate('end_date', '<', $today->toDateString())
                                           ->get();

        foreach ($contractsPastEndDate as $contract) {
            $originalStatus = $contract->status;
            $contract->status = 'expired';
            $contract->last_updated_by = null; // Or a system user ID
            $contract->save();

            // Optionally, update the related shop request if it was fulfilled by this contract
            if ($contract->shop_request_id && $contract->shopRequest) {
                // Check if the shop request was previously 'fulfilled' by this contract
                if ($contract->shopRequest->status === 'fulfilled' && $contract->shopRequest->assigned_contract_id === $contract->id) {
                    $contract->shopRequest->status = 'approved'; // Revert to 'approved' or set to a custom status like 'needs_new_contract'
                    $contract->shopRequest->assigned_contract_id = null;
                    $contract->shopRequest->save();
                    Log::info("ShopRequest ID: {$contract->shopRequest->id} status updated to 'approved' and unlinked due to contract ID: {$contract->id} expiring.");
                }
            }
            Log::info("Contract ID: {$contract->id} (was {$originalStatus}) status set to expired as its end_date ({$contract->end_date->toDateString()}) has passed.");
            $expiredContractsCount++;
        }
        $this->info($expiredContractsCount . ' contracts set to expired.');

        Log::info('Shop Contract Lifecycle Management finished.');
        $this->info('Shop Contract Lifecycle Management finished.');
        return self::SUCCESS;
    }
}
