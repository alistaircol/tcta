<?php
namespace App;

use Carbon\Carbon;

trait DateHelper
{
    /**
     * Determines the next year which is a leap year.
     *
     * @param Carbon $date
     * @return int
     */
    public function nextLeapYear(Carbon $date): int
    {
        $year = $date->year;
        $years_until_next_candidate_leap_year = $year % 4;

        if ($years_until_next_candidate_leap_year == 0) {
            $years_until_next_candidate_leap_year = 4;
        } else {
            $years_until_next_candidate_leap_year = abs($years_until_next_candidate_leap_year - 4);
        }

        $next_leap_year = clone $date;
        $next_leap_year->modify('+' . $years_until_next_candidate_leap_year . ' years');

        if ($next_leap_year->isLeapYear()) {
            return $next_leap_year->year;
        }

        do {
            $next_leap_year->modify('+4 years');
        } while (!$next_leap_year->isLeapYear());

        return $next_leap_year->year;
    }

    private function isForbiddenDate(Carbon $date): bool
    {
        return $this->isWeekend($date)
            || $this->isHoliday($date);
    }

    private function isHoliday(Carbon $date): bool
    {
        return $this->isBoxingDay($date)
            || $this->isChristmasDay($date)
            || $this->isNewYearsDay($date);
    }

    private function isWeekend(Carbon $date): bool
    {
        return $this->isSaturday($date)
            || $this->isSunday($date);
    }

    private function moveIfHoliday(Carbon $date): Carbon
    {
        // Check if provisional cake day falls on a holiday
        if ($this->isNewYearsDay($date)) {
            $date->modify('+1 day');
        } else if ($this->isChristmasDay($date)) {
            // If it's christmas, move 2 days forward (skipping boxing day).
            $date->modify('+2 days');
        } else if ($this->isBoxingDay($date)) {
            $date->modify('+1 day');
        }

        return $date;
    }

    private function moveIfWeekend(Carbon $date): Carbon
    {
        if ($this->isSaturday($date)) {
            $date->modify('+2 days');
        } else if ($this->isSunday($date)) {
            $date->modify('+1 days');
        }

        return $date;
    }

    private function isSaturday(Carbon $date): bool
    {
        return $date->dayOfWeek == 6;
    }

    private function isSunday(Carbon $date): bool
    {
        return $date->dayOfWeek == 0;
    }

    private function isChristmasDay(Carbon $date): bool
    {
        return $date->month == 12 && $date->day == 25;
    }

    private function isBoxingDay(Carbon $date): bool
    {
        return $date->month == 12 && $date->day == 26;
    }

    private function isNewYearsDay(Carbon $date): bool
    {
        return $date->month == 1 && $date->day == 1;
    }

    private function getNextWorkingDay(Carbon $date): Carbon
    {
        $next_working_day = clone $date;
        $next_working_day->modify('+1 day');
        do {
            $next_working_day = $this->moveIfWeekend($next_working_day);
            $next_working_day = $this->moveIfHoliday($next_working_day);
        } while ($this->isForbiddenDate($next_working_day));

        return $next_working_day;
    }
}
