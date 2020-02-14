<?php
namespace App;
use Carbon\Carbon;

class Cake
{
    /**
     * Gets the persons provisional cake date.
     *
     * This will take into account if the birthday falls on a weekend or a holiday, it will be given
     * the next day. Does not consider other Persons birthdays.
     *
     * An employee gets their birthday off
     *
     * @param Person $person
     * @return Carbon
     */
    public static function getCakeDate(Person $person): Carbon
    {
        $birthday = $person->getBirthday();

        // The office is closed on weekends and certain holidays (see isForbiddenDate)
        // An employee gets their birthday off, if the office is closed it's the next working day
        $working_day_off_for_birthday = clone $birthday;

        // Find the next working day which the employee can get off
        do {
            $working_day_off_for_birthday = self::moveIfWeekend($working_day_off_for_birthday);
            $working_day_off_for_birthday = self::moveIfHoliday($working_day_off_for_birthday);
        } while (self::isForbiddenDate($working_day_off_for_birthday));

        // The cake day will be taken the next working day
        $cake_date = clone $working_day_off_for_birthday;
        $cake_date->modify('+1 day');

        do {
            $cake_date = self::moveIfWeekend($cake_date);
            $cake_date = self::moveIfHoliday($cake_date);
        } while (self::isForbiddenDate($cake_date));

        return $cake_date;
    }

    private static function isForbiddenDate(Carbon $date): bool
    {
        return self::isWeekend($date)
            || self::isHoliday($date);
    }

    private static function isHoliday(Carbon $date): bool
    {
        return self::isBoxingDay($date)
            || self::isChristmasDay($date)
            || self::isNewYearsDay($date);
    }

    private static function isWeekend(Carbon $date): bool
    {
        return self::isSaturday($date)
            || self::isSunday($date);
    }

    private static function moveIfHoliday(Carbon $date): Carbon
    {
        // Check if provisional cake day falls on a holiday
        if (self::isNewYearsDay($date)) {
            $date->modify('+1 day');
        } else if (self::isChristmasDay($date)) {
            // If it's christmas, move 2 days forward (skipping boxing day).
            $date->modify('+2 days');
        } else if (self::isBoxingDay($date)) {
            $date->modify('+1 day');
        }

        return $date;
    }

    private static function moveIfWeekend(Carbon $date): Carbon
    {
        if (self::isSaturday($date)) {
            $date->modify('+2 days');
        } else if (self::isSunday($date)) {
            $date->modify('+1 days');
        }

        return $date;
    }

    private static function isSaturday(Carbon $date): bool
    {
        return $date->dayOfWeek == 6;
    }

    private static function isSunday(Carbon $date): bool
    {
        return $date->dayOfWeek == 0;
    }

    private static function isChristmasDay(Carbon $date): bool
    {
        return $date->month == 12 && $date->day == 25;
    }

    private static function isBoxingDay(Carbon $date): bool
    {
        return $date->month == 12 && $date->day == 26;
    }

    private static function isNewYearsDay(Carbon $date): bool
    {
        return $date->month == 1 && $date->day == 1;
    }
}
