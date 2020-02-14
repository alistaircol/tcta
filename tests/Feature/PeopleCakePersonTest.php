<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use App\Person;
use App\Cake;
use App\CakeDistribution;
use Carbon\Carbon;

final class PeopleCakePersonTest extends TestCase
{
    public function testSamBorn13JulyKateBorn14JulyShareALargeCakeOn15July()
    {
        $sam  = (new Person())->name('Sam')->dob(new Carbon('2020-07-13'));
        $kate = (new Person())->name('Kate')->dob(new Carbon('2020-07-14'));

        $people = new ArrayIterator([], ArrayIterator::STD_PROP_LIST);
        $people->append($sam);
        $people->append($kate);

        $cake_distribution = new CakeDistribution();
        $cake_distribution->setPeople($people);

        $this->markTestIncomplete('TODO: Cake Distribution');
    }
}
