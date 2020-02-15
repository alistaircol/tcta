<?php
namespace App;

use Carbon\Carbon;
use ArrayIterator;

class CakeDistribution
{
    use DateHelper;

    private array $distribution = [];
    private \CachingIterator $people;
    private \SplStack $stack;

    /**
     * Set an array iterator containing all people.
     *
     * @throws \Exception
     * @param \ArrayIterator $people
     */
    public function setPeople(ArrayIterator $people)
    {
        $this->people = new \CachingIterator($people);
    }

    /**
     * Get cake distribution from people.
     *
     * @return array
     * @throws \Exception
     */
    public function getDistribution()
    {
        // Sort people with the birthdays latest in the year first,
        // so soonest birthdays pushed onto the stack are at the top.
        $this->stack = new \SplStack();
        $this->people->uasort(function ($a, $b) {
           return $b->getCakeDate() <=> $a->getCakeDate();
        });

        foreach ($this->people as $person) {
            $this->stack->push($person);
        }

        do {
            $current_person = $this->stack->pop();

            // If there are some cakes already allocated then we may need to alter
            // future cake dates to consider the cake free day
            $current_cake_date = $current_person->getCakeDate()->format('Y-m-d');
            if (!empty($this->distribution)) {
                $last_cake_day = array_key_last($this->distribution);
                $cake_free_day = (new Carbon($last_cake_day))->modify('+1 day');
                $next_available_cake_day = $this->getNextWorkingDay($cake_free_day);

                // Again, if there is previous distribution we set $current_cake_date
                // to be the next available date if the next cake day is behind this date.
                if ($current_person->getCakeDate()->lessThan($next_available_cake_day)) {
                    $current_cake_date = $next_available_cake_day->format('Y-m-d');
                }
            }

            // Get the next working date to see whether the next cake day falls on this day,
            // if so a large cake is given on the second day to share the consequent cake days
            $next_working_day = $this->getNextWorkingDay($current_person->getCakeDate())->format('Y-m-d');

            // If the stack is empty then this is all we need to do
            if ($this->stack->isEmpty()) {
                $this->distribution[$current_cake_date] = Cake::small()
                    ->date(new Carbon($current_cake_date))
                    ->people([$current_person]);

                continue;
            }

            $next_person = $this->stack->top();
            $next_person_cake_date = $next_person->getCakeDate()->format('Y-m-d');

            if ($current_cake_date == $next_person_cake_date) {
                // provide a large one to share since they share same date
                $this->distribution[$current_cake_date] = Cake::large()
                    ->date(new Carbon($current_cake_date))
                    ->people([
                        $current_person,
                        $next_person,
                    ]);

                // Remove current person from the stack
                $this->stack->pop();
            } else if ($next_working_day == $next_person_cake_date) {
                // provide a large one on the next_working_day since there are subsequent cake days
                $this->distribution[$next_working_day] = Cake::large()
                    ->date(new Carbon($next_working_day))
                    ->people([
                        $current_person,
                        $next_person,
                    ]);

                // Remove current person from the stack
                $this->stack->pop();
            } else {
                // No one sharing cake day on same day or the next working day so they get a small one to themselves
                $this->distribution[$current_cake_date] = Cake::small()
                    ->date(new Carbon($current_cake_date))
                    ->people([$current_person]);
            }

        } while (!$this->stack->isEmpty());

        // Check the distribution and make sure only current years cake days are included
        $current_year = date('Y');
        $years_distribution = [];
        foreach ($this->distribution as $cake_date => $cake) {
            if (substr($cake_date, 0, 4) == $current_year) {
                $years_distribution[$cake_date] = $cake;
            }
        }

        return $years_distribution;
    }

}
