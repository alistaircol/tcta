<?php
namespace App;

use Carbon\Carbon;

class CakeDistribution
{
    private \ArrayIterator $people;

    /**
     * Set an array iterator containing all people.
     *
     * @param \ArrayIterator $people
     */
    public function setPeople(\ArrayIterator $people)
    {
        $this->people = $people;
    }

    public function getDistribution()
    {
        $current_year = date('Y');
        // TODO: process all peoples cake days and shuffle stuff
        foreach ($this->people as $person) {
            //
        }
    }
}
