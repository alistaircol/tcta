<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Carbon\Carbon;
use App\DateHelper;
use App\Person;

/**
 * Class NextLeapYearTest
 *
 * if (year is not divisible by 4) then (it is a common year)
 * else if (year is not divisible by 100) then (it is a leap year)
 * else if (year is not divisible by 400) then (it is a common year)
 * else (it is a leap year)
 */
final class NextLeapYearTest extends TestCase
{
    use DateHelper;

    /**
     * Next leap year is straightforward.
     *
     * @throws Exception
     */
    public function testNextLeapYearFrom2019Is2020()
    {
        $year2019 = new Carbon('2019-01-01');

        $this->assertEquals(2020, $this->nextLeapYear($year2019));
    }

    /**
     * Next leap year is 2000 because it's divisible by 4, 100 and 400
     *
     * @throws Exception
     */
    public function testNextLeapYearFrom1997is2000()
    {
        $year1997 = new Carbon('1997-01-01');
        $this->assertEquals(2000, $this->nextLeapYear($year1997));
    }

    /**
     * Next leap year is 2104 and not 2100 because 2100 it's not divisble by 400
     */
    public function testNextLeapYearFrom2097is2104()
    {
        $year2097 = new Carbon('2097-01-01');
        $this->assertEquals(2104, $this->nextLeapYear($year2097));
    }

    /**
     * @throws Exception
     */
    public function testPersonBirthdayOnLeapYear()
    {
        $person = (new Person())->name('Leapling')->dob(new Carbon('2016-02-29'));
        $this->assertEquals('2020-02-29', $person->getBirthday()->format('Y-m-d'));
    }

    /**
     * @throws Exception
     */
    public function testPersonBornInLeapYearWillHaveBirthdayInNextLeapYearWhenRequestedInBetweenDates()
    {
        $person = (new Person())->name('Leapling')->dob(new Carbon('2016-02-29'));
        $this->assertEquals('2020-02-29', $person->getBirthday(2019)->format('Y-m-d'));

        $person = (new Person())->name('Leapling')->dob(new Carbon('2096-02-29'));
        $this->assertEquals('2104-02-29', $person->getBirthday(2100)->format('Y-m-d'));
    }
}
