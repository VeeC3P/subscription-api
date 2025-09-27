<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Subscription;

class SubscriptionTransitionRequest extends FormRequest
{
    public function authorize()
    {
        // adjust authorization as needed (e.g., ensure user owns subscription)
        return true;
    }

    public function rules()
    {
        return [
            'phase' => ['required', 'string', 'in:' . implode(',', Subscription::$PHASES)],
        ];
    }
}
