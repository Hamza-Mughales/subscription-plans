<?php

declare(strict_types=1);

use Carbon\Carbon;
use NootPro\SubscriptionPlans\Services\Period;

it('can create a period with default values', function () {
    $period = new Period;

    expect($period)
        ->getInterval()->toBe('month')
        ->getIntervalCount()->toBe(1)
        ->getStartDate()->toBeInstanceOf(Carbon::class)
        ->getEndDate()->toBeInstanceOf(Carbon::class);
});

it('can create a period with custom interval', function () {
    $period = new Period('day', 7);

    expect($period)
        ->getInterval()->toBe('day')
        ->getIntervalCount()->toBe(7);

    $expectedEnd = $period->getStartDate()->copy()->addDays(7);
    expect($period->getEndDate()->format('Y-m-d'))->toBe($expectedEnd->format('Y-m-d'));
});

it('can create a period with custom start date', function () {
    $startDate = Carbon::parse('2025-01-01');
    $period    = new Period('month', 1, $startDate);

    expect($period->getStartDate()->format('Y-m-d'))->toBe('2025-01-01');
    expect($period->getEndDate()->format('Y-m-d'))->toBe('2025-02-01');
});

it('throws exception for invalid interval', function () {
    new Period('invalid-interval', 1);
})->throws(InvalidArgumentException::class, 'Invalid interval');

it('throws exception for negative count', function () {
    new Period('month', -1);
})->throws(InvalidArgumentException::class, 'Count must be a positive integer');

it('calculates correct end date for different intervals', function ($interval, $count, $expectedDays) {
    $start  = Carbon::parse('2025-01-01');
    $period = new Period($interval, $count, $start);

    $actualDays = (int) $start->diffInDays($period->getEndDate());
    expect($actualDays)->toBe($expectedDays);
})->with([
    ['day', 1, 1],
    ['day', 7, 7],
    ['week', 1, 7],
    ['week', 2, 14],
    ['month', 1, 31],
    ['year', 1, 365],
]);
