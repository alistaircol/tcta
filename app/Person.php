<?php
namespace App;
use Carbon\Carbon;

class Person
{
    protected string $name;
    protected Carbon $date_of_birth;

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
     * @return Person
     */
    public function dob(Carbon $date_of_birth): self
    {
        $this->date_of_birth = $date_of_birth;
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
     * Get this persons birthday for the current year.
     *
     * @return Carbon
     */
    public function getBirthday(): Carbon
    {
        return Carbon::createFromFormat('Y-m-d', vsprintf('%d-%d-%d', [
            date('Y'),
            $this->getDob()->month,
            $this->getDob()->day,
        ]));
    }
}
