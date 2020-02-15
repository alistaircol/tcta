<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use App\Person;
use App\Cake;
use Carbon\Carbon;

final class PersonCakeDateTest extends TestCase
{
    public function testDaveBorn26June1986BirthdayFriday26JuneGetsACakeOnMonday29June2020()
    {
        // Dave born 26th June 1986
        $dave = (new Person())->name('Dave')->dob(new Carbon('1986-06-26'));

        $this->assertEquals('Dave', $dave->getName());
        $this->assertEquals((new Carbon('1986-06-26'))->toString(), $dave->getDob()->toString());

        // The birthday is Fri 26th June 2020
        $this->assertEquals('2020-06-26', $dave->getBirthday()->format('Y-m-d'));

        // The cake day will be Sat 27th (day after birthday) but will be moved to 29th (first working day after)
        $dave_cake_date = $dave->getCakeDate();
        $this->assertEquals('2020-06-29', $dave_cake_date->format('Y-m-d'));
    }

    public function testRobBorn5July1950BirthdaySunday5JulyGetsACakeOnTuesday7July2020()
    {
        // Rob born 5th July 1950
        $rob = (new Person())->name('Rob')->dob(new Carbon('1950-07-05'));

        $this->assertEquals('Rob', $rob->getName());
        $this->assertEquals((new Carbon('1950-07-05'))->toString(), $rob->getDob()->toString());

        // The birthday is Sun 5th July 2020
        $this->assertEquals('2020-07-05', $rob->getBirthday()->format('Y-m-d'));

        // The cake day will be Tue 7th July. Moved to monday (employee is off on birthday) and then moved to tuesday.
        $rob_cake_date = $rob->getCakeDate();
        $this->assertEquals('2020-07-07', $rob_cake_date->format('Y-m-d'));
    }

    public function testBorn1JanuaryBirthday1January2020GetsACakeOnFriday3January2020()
    {
        $person = (new Person())->name('01/01')->dob(new Carbon('2020-01-01'));
        $cake_date = $person->getCakeDate();

        // office closed 01/01, cake date moves to 02/01 (employee is off), next working day is 03/01
        $this->assertEquals('2020-01-03', $cake_date->format('Y-m-d'));
    }

    public function testBorn24DecemberBirthdayThursday24GetsACakeOnMonday28December2020()
    {
        $person = (new Person())->name('23/12')->dob(new Carbon('2020-12-24'));
        $cake_date = $person->getCakeDate();

        // employee off on 24th, 25th & 26th holidays, 27th is sunday, 28th is next working day
        $this->assertEquals('2020-12-28', $cake_date->format('Y-m-d'));
    }

    public function testBornThursday31DecemberBirthdayThursday31GetsCakeOnMonday4January2021()
    {
        $person = (new Person())->name('31/12')->dob(new Carbon('2012-12-31'));
        $cake_date = $person->getCakeDate();

        // employee off on thu 31st, holiday fri 1st, weekend 2nd & 3rd
        $this->assertEquals('2021-01-04', $cake_date->format('Y-m-d'));
    }
}
