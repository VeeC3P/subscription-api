<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_422_on_invalid_transition()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create(['phase' => Subscription::PHASE_CANCELED, 'user_id' => $user->id]);

        $response = $this->postJson("/api/subscriptions/{$sub->id}/transition", ['phase' => Subscription::PHASE_ACTIVE]);

        $response->assertStatus(422);
    }

    public function test_api_returns_200_on_valid_transition()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create(['phase' => Subscription::PHASE_TRIAL, 'user_id' => $user->id]);

        $response = $this->postJson("/api/subscriptions/{$sub->id}/transition", ['phase' => Subscription::PHASE_ACTIVE]);

        $response->assertStatus(200)
                 ->assertJson(['phase' => Subscription::PHASE_ACTIVE]);
    }
}
