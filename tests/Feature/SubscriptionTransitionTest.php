<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\SubscriptionService;
use App\Exceptions\InvalidTransitionException;
use Carbon\Carbon;

class SubscriptionTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_trial_transitions_to_active_after_time()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->for($user)->create([
            'phase' => Subscription::PHASE_TRIAL,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'price' => 100.00,
        ]);

        $service = $this->app->make(SubscriptionService::class);
        $service->transitionToNextPhase($sub, Subscription::PHASE_ACTIVE, Carbon::now());

        $this->assertEquals(Subscription::PHASE_ACTIVE, $sub->fresh()->phase);
        $this->assertNotNull($sub->fresh()->start_date);
    }

    public function test_active_can_be_suspended_or_canceled()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->for($user)->create([
            'phase' => Subscription::PHASE_ACTIVE,
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'price' => 100.00,
        ]);

        $service = $this->app->make(SubscriptionService::class);

        $service->transitionToNextPhase($sub, Subscription::PHASE_SUSPENDED);
        $this->assertEquals(Subscription::PHASE_SUSPENDED, $sub->fresh()->phase);

        // suspended -> active
        $service->transitionToNextPhase($sub->fresh(), Subscription::PHASE_ACTIVE);
        $this->assertEquals(Subscription::PHASE_ACTIVE, $sub->fresh()->phase);

        // active -> canceled
        $service->transitionToNextPhase($sub->fresh(), Subscription::PHASE_CANCELED);
        $this->assertEquals(Subscription::PHASE_CANCELED, $sub->fresh()->phase);
    }

    public function test_invalid_transition_throws_exception()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create(['phase' => Subscription::PHASE_CANCELED, 'user_id' => $user->id]);

        $this->expectException(InvalidTransitionException::class);

        $service = $this->app->make(SubscriptionService::class);
        $service->transitionToNextPhase($sub, Subscription::PHASE_ACTIVE);
    }
}
