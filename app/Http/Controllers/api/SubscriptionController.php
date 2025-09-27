<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Http\Requests\SubscriptionTransitionRequest;
use App\Services\SubscriptionService;
use App\Exceptions\InvalidTransitionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $service;

    public function __construct(SubscriptionService $service)
    {
        $this->service = $service;
    }

    // POST /api/subscriptions/{id}/transition
    public function transition(SubscriptionTransitionRequest $request, $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);
        $newPhase = $request->input('phase');

        try {
            $subscription = $this->service->transitionToNextPhase($subscription, $newPhase);
            return response()->json(['phase' => $subscription->phase], 200);
        } catch (InvalidTransitionException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    // GET /api/subscriptions/{id}/amount?usedUntil=YYYY-MM-DD
    public function amount(Request $request, $id): JsonResponse
    {
        $subscription = Subscription::findOrFail($id);
        $usedUntil = $request->query('usedUntil', now()->toDateString());

        try {
            $amount = $subscription->calculateProRatedAmount($usedUntil);
            return response()->json(['amount' => number_format($amount, 2, '.', '')], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date or calculation error'], 422);
        }
    }
}
