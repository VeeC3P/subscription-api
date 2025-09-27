<?php

namespace Database\Factories;

use App\Models\Subscription;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use Carbon\Carbon;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'phase' => $this->faker->randomElement([Subscription::PHASE_TRIAL, Subscription::PHASE_ACTIVE]),
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => null,
            'price' => 100.00,
        ];
    }

    public function trial()
    {
        return $this->state(fn () => ['phase' => Subscription::PHASE_TRIAL]);
    }

    public function active()
    {
        return $this->state(fn () => ['phase' => Subscription::PHASE_ACTIVE]);
    }

}
