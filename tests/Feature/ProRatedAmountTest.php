<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProRatedAmountTest extends TestCase
{
    use RefreshDatabase;

    public function test_pro_rata_15_days_of_30_is_half()
    {
        $user = User::factory()->create();
        // Choose February-like 30-day month for test? We'll simulate a 30-day month by setting a start_date in a 30-day month, e.g., June (30 days).
        $sub = Subscription::factory()->create([
            'start_date' => '2025-06-01', // June has 30 days
            'user_id' => $user->id,
            'price' => 100.00,
        ]);

        // usedUntil = 2025-06-15 => usedDays = 15 (from 1 to 15 inclusive)
        $amount = $sub->calculateProRatedAmount('2025-06-15');

        // 100 * 15/30 = 50.00
        $this->assertEquals(50.00, $amount);
    }

    public function test_amount_api_endpoint_returns_amount()
    {
        $user = User::factory()->create();
        $sub = Subscription::factory()->create([
            'start_date' => '2025-06-01',
            'user_id' => $user->id,
            'price' => 100.00,
        ]);

        $response = $this->getJson("/api/subscriptions/{$sub->id}/amount?usedUntil=2025-06-15");
        $response->assertStatus(200)
                 ->assertJson(['amount' => '50.00']);
    }
}
