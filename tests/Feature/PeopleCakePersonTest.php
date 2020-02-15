<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use App\Person;
use App\CakeDistribution;
use App\Cake;
use Carbon\Carbon;

final class PeopleCakePersonTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSamBorn13JulyKateBorn14JulyShareALargeCakeOn15July()
    {
        $sam  = (new Person())->name('Sam')->dob(new Carbon('2020-07-13'));
        $kate = (new Person())->name('Kate')->dob(new Carbon('2020-07-14'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($kate);
        $people->append($sam);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(1, $distribution);
        $key = '2020-07-15';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('large', $distribution[$key]->getCakeSize());
        $this->assertCount(2, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'Sam'));
        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'Kate'));
    }

    /**
     * @throws Exception
     */
    public function testTwoBornSameDateWillShareALargeCakeTheNextWorkingDay()
    {
        // Monday 10th
        $a = (new Person())->name('A')->dob(new Carbon('2020-02-10'));
        $b = (new Person())->name('B')->dob(new Carbon('2020-02-10'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($a);
        $people->append($b);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(1, $distribution);
        $key = '2020-02-11';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('large', $distribution[$key]->getCakeSize());
        $this->assertCount(2, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'A'));
        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'B'));
    }

    /**
     * @throws Exception
     */
    public function testThreeBornSameDateWillShareALargeCakeTheNextWorkingDayAndTheThirdWillHaveASmallCake2WorkingDaysLater()
    {
        // Monday 10th
        $a = (new Person())->name('A')->dob(new Carbon('2020-02-10'));
        $b = (new Person())->name('B')->dob(new Carbon('2020-02-10'));
        $c = (new Person())->name('C')->dob(new Carbon('2020-02-10'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($a);
        $people->append($b);
        $people->append($c);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(2, $distribution);
        $key = '2020-02-11';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('large', $distribution[$key]->getCakeSize());
        $this->assertCount(2, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'C'));
        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'B'));

        // Ensure there is a day between cakes
        $cake_free_day = '2020-02-12';
        $this->assertArrayNotHasKey($cake_free_day, $distribution);

        $key = '2020-02-13';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('small', $distribution[$key]->getCakeSize());
        $this->assertCount(1, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'A'));
    }

    /**
     * @throws Exception
     */
    public function testAlexBorn20JulyJenBorn21JulyPeteBorn22JulyThatAlexAndJenShareLargeCake22JulyAndPeteHasSmallCakeOn24July()
    {
        $alex = (new Person())->name('Alex')->dob(new Carbon('2020-07-20'));
        $jen  = (new Person())->name('Jen')->dob(new Carbon('2020-07-21'));
        $pete = (new Person())->name('Pete')->dob(new Carbon('2020-07-22'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($alex);
        $people->append($jen);
        $people->append($pete);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(2, $distribution);
        $key = '2020-07-22';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('large', $distribution[$key]->getCakeSize());
        $this->assertCount(2, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'Alex'));
        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'Jen'));

        // Ensure there is a day between cakes
        $cake_free_day = '2020-07-23';
        $this->assertArrayNotHasKey($cake_free_day, $distribution);

        $key = '2020-07-24';
        $this->assertArrayHasKey($key, $distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('small', $distribution[$key]->getCakeSize());
        $this->assertCount(1, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'Pete'));
    }

    /**
     * @throws Exception
     */
    public function testOnePersonBornDayBeforeTwoOthersShareSameBirthdayThatALargeCakeIsGivenOnSecondDayToShareAndOnePersonFromSharedBirthdayWillReceiveSmallCake()
    {
        // Cake on Monday 11th (provisionally)
        $a = (new Person())->name('A')->dob(new Carbon('2020-02-10'));
        // Cake on Tuesday 12th (provisionally)
        $b = (new Person())->name('B')->dob(new Carbon('2020-02-11'));
        $c = (new Person())->name('C')->dob(new Carbon('2020-02-11'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($a);
        $people->append($b);
        $people->append($c);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(2, $distribution);

        // A will not get cake today because B means a large is provided on B day to share
        $key = '2020-02-11';
        $this->assertArrayNotHasKey($key, $distribution);

        $key = '2020-02-12';
        $this->assertArrayHasKey($key,$distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('large', $distribution[$key]->getCakeSize());
        $this->assertCount(2, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'A'));
        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'B'));

        // Cake free day
        $key = '2020-02-13';
        $this->assertArrayNotHasKey($key, $distribution);

        $key = '2020-02-14';
        $this->assertArrayHasKey($key,$distribution);
        $this->assertInstanceOf(Cake::class, $distribution[$key]);
        $this->assertEquals('small', $distribution[$key]->getCakeSize());
        $this->assertCount(1, $distribution[$key]->getPeople());

        $this->assertTrue($this->hasPerson($distribution[$key]->getPeople(), 'C'));
    }

    public function testOnePersonBornEndOfYearIsNotIncudedInCurrentYearDistributionSinceItIsMovedToNewYear()
    {
        $a = (new Person())->name('A')->dob(new Carbon('2020-12-31'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($a);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $distribution = $cake_distribution->getDistribution();

        $this->assertCount(0, $distribution);
    }

    /**
     * @param array $people array of people (returned from distribution)
     * @param string $name
     * @return bool
     */
    private function hasPerson(array $people, string $name): bool
    {
        $found = false;

        foreach ($people as $person) {
            if ($person->getName() == $name) {
                $found = true;
            }
        }

        return $found;
    }
}
