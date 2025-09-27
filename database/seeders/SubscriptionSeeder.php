<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        // Dont make loads of users for no reason
        $users = User::take(2)->get();

        $user1 = $users[0] ?? null;
        $user2 = $users[1] ?? null;

        if (! $user1 || ! $user2) {
            // Fallback: create missing users
            $user1 ??= User::factory()->create();
            $user2 ??= User::factory()->create();
        }

        Subscription::factory()->for($user1)->create([
            'phase' => Subscription::PHASE_TRIAL,
            'start_date' => Carbon::now()->subDays(5)->toDateString(),
            'price' => 100.00,
        ]);

        Subscription::factory()->for($user2)->create([
            'phase' => Subscription::PHASE_ACTIVE,
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'price' => 100.00,
        ]);
    }
}
