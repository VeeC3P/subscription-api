<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    public const PHASE_TRIAL = 'trial';
    public const PHASE_ACTIVE = 'active';
    public const PHASE_SUSPENDED = 'suspended';
    public const PHASE_CANCELED = 'canceled';

    public static array $PHASES = [
        self::PHASE_TRIAL,
        self::PHASE_ACTIVE,
        self::PHASE_SUSPENDED,
        self::PHASE_CANCELED,
    ];

    protected $fillable = [
        'user_id',
        'phase',
        'start_date',
        'end_date',
        'price',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // calculate pro-rated amount: $usedUntil is Carbon|date-string
    public function calculateProRatedAmount($usedUntil): float
    {
        $price = (float) $this->price;
        $usedUntil = $usedUntil instanceof Carbon ? $usedUntil : Carbon::parse($usedUntil);

        // Determine period: if start_date exists, use that month; otherwise assume current month from now()
        $start = $this->start_date ? Carbon::parse($this->start_date) : $usedUntil->copy()->startOfMonth();
        // We calculate proportion of days used in that month (from start to usedUntil).
        $periodStart = $start->copy()->startOfMonth();
        $periodEnd = $start->copy()->endOfMonth();

        // If subscription started after the month start, respect that
        if ($this->start_date && $this->start_date->greaterThan($periodStart)) {
            $periodStart = $this->start_date->copy();
        }

        // Bound usedUntil inside the month/end
        if ($usedUntil->greaterThan($periodEnd)) {
            $usedUntil = $periodEnd->copy();
        }

        if ($usedUntil->lessThan($periodStart)) {
            return 0.0;
        }

        // days counting: include both start and usedUntil as used days? Common approach: number of days used = usedUntil->diffInDays(periodStart) + 1
        $usedDays = $periodStart->diffInDays($usedUntil) + 1;
        $daysInMonth = $periodStart->daysInMonth;
        
        $amount = ($price / $daysInMonth) * $usedDays;

        return round($amount, 2);
    }
}
