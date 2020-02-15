<?php
namespace App;

use Carbon\Carbon;

class Cake
{
    private string $cake_size;
    private Carbon $cake_date;
    private array $people;

    public static function small(): self
    {
        $cake = new static();
        $cake->cake_size = 'small';
        return $cake;
    }

    public static function large()
    {
        $cake = new static();
        $cake->cake_size = 'large';
        return $cake;
    }

    public function date(Carbon $cake_date): self
    {
        $this->cake_date = $cake_date;
        return $this;
    }

    public function people(array $people): self
    {
        $this->people = $people;
        return $this;
    }

    public function getCakeSize(): string
    {
        return $this->cake_size;
    }

    public function getCakeDate(): Carbon
    {
        return $this->cake_date;
    }

    public function getPeople(): array
    {
        return $this->people;
    }
}
