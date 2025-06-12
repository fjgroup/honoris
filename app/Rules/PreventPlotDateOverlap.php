<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\ShopContract;
// use App\Models\MapPlot; // Not strictly needed for this rule's logic but good for context
use Carbon\Carbon;

class PreventPlotDateOverlap implements Rule
{
    protected $mapPlotId;
    protected $startDate;
    protected $endDate;
    protected $currentContractId; // To ignore the current contract when updating

    /**
     * Create a new rule instance.
     *
     * @param mixed $mapPlotId
     * @param mixed $startDate
     * @param mixed $endDate
     * @param mixed $currentContractId
     * @return void
     */
    public function __construct($mapPlotId, $startDate, $endDate, $currentContractId = null)
    {
        $this->mapPlotId = $mapPlotId;
        // Ensure dates are parsed, null if input is invalid/empty
        try {
            $this->startDate = $startDate ? Carbon::parse($startDate) : null;
            $this->endDate = $endDate ? Carbon::parse($endDate) : null;
        } catch (\Exception $e) {
            $this->startDate = null;
            $this->endDate = null;
        }
        $this->currentContractId = $currentContractId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute Usually 'map_plot_id' or a date field, but the logic uses constructor params
     * @param  mixed  $value The value of the attribute being validated (e.g. map_plot_id)
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // If essential data for the rule is missing (e.g., dates couldn't be parsed or map_plot_id is null),
        // we assume other rules (like 'required', 'date', 'exists') should catch these.
        // This rule specifically checks for overlap if all data is present and valid so far.
        if (!$this->mapPlotId || !$this->startDate || !$this->endDate) {
            // If dates are invalid and parsing failed, this rule should not block,
            // letting 'date' rule fail first.
            return true;
        }
        // Also, if end_date is before start_date, let 'after_or_equal' rule handle it.
        if ($this->endDate->isBefore($this->startDate)) {
            return true;
        }


        $query = ShopContract::where('map_plot_id', $this->mapPlotId)
            ->whereIn('status', ['active', 'pending_renewal']) // Check against active/pending contracts
            ->where(function ($query) {
                // Logic from problem description:
                // 1. New contract starts during an existing contract
                $query->where(function ($q) {
                    $q->where('start_date', '<=', $this->startDate)
                      ->where('end_date', '>=', $this->startDate);
                })
                // 2. New contract ends during an existing contract
                ->orWhere(function ($q) {
                    $q->where('start_date', '<=', $this->endDate)
                      ->where('end_date', '>=', $this->endDate);
                })
                // 3. New contract completely envelops an existing contract
                ->orWhere(function ($q) {
                    $q->where('start_date', '>=', $this->startDate)
                      ->where('end_date', '<=', $this->endDate);
                });
            });

        if ($this->currentContractId) {
            $query->where('id', '!=', $this->currentContractId);
        }

        return !$query->exists(); // Passes if no such overlapping contract exists
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return 'This map plot is already under an active or pending renewal contract that overlaps with the selected dates.';
    }
}
