<?php

namespace WeekDev\Collections;

use ArrayIterator;
use WeekDev\Classes\Elevator;

final class Elevators extends ArrayIterator
{
    public function offsetGet(mixed $key): ?Elevator
    {
        return parent::offsetGet($key);
    }
}