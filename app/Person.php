<?php
namespace App;
use Carbon\Carbon;

class Person
{
    use DateHelper;

    protected string $name;
    protected Carbon $date_of_birth;
    protected Carbon $cake_date;

    /**
     * Set the persons name.
     *
     * @param string $name
     * @return Person
     */
    public function name(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Set the persons date of birth.
     *
     * @param Carbon $date_of_birth
     * @throws \Exception
     * @return Person
     */
    public function dob(Carbon $date_of_birth): self
    {
        $this->date_of_birth = $date_of_birth;
        $this->calculateCakeDate();
        return $this;
    }

    /**
     * Get the persons name.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the persons date of birth.
     *
     * @return Carbon
     */
    public function getDob(): Carbon
    {
        return $this->date_of_birth;
    }

    /**
     * @return Carbon
     * @throws \Exception
     */
    public function getBirthday(int $year = null): Carbon
    {
        if (!$year) {
            $today = new Carbon();
        } else {
            $today = new Carbon($year . '-' . date('m-d'));
        }

        // if it's a leap year then deal with that..
        if ($this->getDob()->month == 2 && $this->getDob()->day == 29 && !$today->isLeapYear()) {
            $year = $this->nextLeapYear($today);
            return Carbon::createFromFormat('Y-m-d', vsprintf('%d-%d-%d', [
                $year,
                2,
                29
            ]));
        }

        return Carbon::createFromFormat('Y-m-d', vsprintf('%d-%d-%d', [
            $today->year,
            $this->getDob()->month,
            $this->getDob()->day,
        ]));
    }

    /**
     * Gets the persons provisional cake date.
     *
     * This will take into account if the birthday falls on a weekend or a holiday, it will be given
     * the next day. Does not consider other Persons birthdays.
     *
     * An employee gets their birthday off
     *
     * @throws \Exception
     * @return Carbon
     */
    public function getCakeDate(): Carbon
    {
        return $this->cake_date;
    }

    /**
     * Calculate the provisional cake date.
     *
     * This will take into account if the birthday falls on a weekend or a holiday, it will be given
     * the next day. Does not consider other Persons birthdays.
     *
     * An employee gets their birthday off
     *
     * @throws \Exception
     */
    private function calculateCakeDate()
    {
        $birthday = $this->getBirthday();

        // The office is closed on weekends and certain holidays (see isForbiddenDate)
        // An employee gets their birthday off, if the office is closed it's the next working day
        $working_day_off_for_birthday = clone $birthday;

        // Find the next working day which the employee can get off
        do {
            $working_day_off_for_birthday = $this->moveIfWeekend($working_day_off_for_birthday);
            $working_day_off_for_birthday = $this->moveIfHoliday($working_day_off_for_birthday);
        } while ($this->isForbiddenDate($working_day_off_for_birthday));

        // The cake day will be taken the next working day
        $cake_date = clone $working_day_off_for_birthday;
        $cake_date->modify('+1 day');

        do {
            $cake_date = $this->moveIfWeekend($cake_date);
            $cake_date = $this->moveIfHoliday($cake_date);
        } while ($this->isForbiddenDate($cake_date));

        $this->cake_date = $cake_date;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
