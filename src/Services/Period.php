<?php

declare(strict_types=1);

namespace NootPro\SubscriptionPlans\Services;

use Carbon\Carbon;
use InvalidArgumentException;

class Period
{
    private Carbon $start;

    private Carbon $end;

    private string $interval;

    private int $period;

    /**
     * Valid interval types.
     */
    private const VALID_INTERVALS = ['hour', 'day', 'week', 'month', 'year'];

    public function __construct(string $interval = 'month', int $count = 1, ?Carbon $start = null)
    {
        // Validate interval
        if (! in_array($interval, self::VALID_INTERVALS, true)) {
            throw new InvalidArgumentException(
                sprintf('Invalid interval "%s". Must be one of: %s', $interval, implode(', ', self::VALID_INTERVALS))
            );
        }

        // Validate count
        if ($count < 0) {
            throw new InvalidArgumentException('Count must be a positive integer.');
        }

        $this->interval = $interval;
        $this->start    = $start ?? Carbon::now();
        $this->period   = $count;

        // Calculate end date
        $method = 'add'.ucfirst($this->interval).'s';

        /** @var Carbon $end */
        $end = (clone $this->start)->{$method}($this->period);

        $this->end = $end;
    }

    public function getStartDate(): Carbon
    {
        return $this->start;
    }

    public function getEndDate(): Carbon
    {
        return $this->end;
    }

    public function getInterval(): string
    {
        return $this->interval;
    }

    public function getIntervalCount(): int
    {
        return $this->period;
    }
}
