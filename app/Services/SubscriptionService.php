<?php

namespace App\Services;

use App\Models\Subscription;
use App\Exceptions\InvalidTransitionException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionService
{
    /**
     * Transition map: allowed next phases
     */
    protected array $map = [
        Subscription::PHASE_TRIAL => [Subscription::PHASE_ACTIVE],
        Subscription::PHASE_ACTIVE => [Subscription::PHASE_SUSPENDED, Subscription::PHASE_CANCELED],
        Subscription::PHASE_SUSPENDED => [Subscription::PHASE_ACTIVE, Subscription::PHASE_CANCELED],
        Subscription::PHASE_CANCELED => [], // no transitions
    ];

    /**
     * Try to transition subscription to $newPhase.
     *
     * @throws InvalidTransitionException
     */
    public function transitionToNextPhase(Subscription $subscription, string $newPhase, ?Carbon $effectiveDate = null): Subscription
    {
        if (! isset($this->map[$subscription->phase])) {
            throw new InvalidTransitionException("Unknown current phase {$subscription->phase}");
        }

        if (! in_array($newPhase, $this->map[$subscription->phase], true)) {
            throw new InvalidTransitionException("Transition from {$subscription->phase} to {$newPhase} is not allowed.");
        }

        // Atomic update
        return DB::transaction(function () use ($subscription, $newPhase, $effectiveDate) {
            $subscription->phase = $newPhase;

            // Update dates: simple behaviour:
            // when going to active -> ensure start_date set (if null) and end_date null
            // when cancel -> set end_date to effectiveDate or today
            $now = $effectiveDate ?? Carbon::now();

            if ($newPhase === Subscription::PHASE_ACTIVE) {
                if (!$subscription->start_date) {
                    $subscription->start_date = $now->toDateString();
                }
                $subscription->end_date = null;
            }

            if ($newPhase === Subscription::PHASE_CANCELED) {
                $subscription->end_date = $now->toDateString();
            }

            // suspended: don't change start_date, set end_date null (we consider it's paused)
            if ($newPhase === Subscription::PHASE_SUSPENDED) {
                $subscription->end_date = null;
            }

            $subscription->save();

            return $subscription;
        });
    }
}
