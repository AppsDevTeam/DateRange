<?php

namespace Danoha;


class DateRangeCollection
{

    /** @var DateRange[] */
    protected $ranges = [];

    /**
     * @param array $collection
     * @throws \InvalidArgumentException
     */
    public function __construct($collection)
    {
        foreach ($collection as $item) {
            $this->ranges[] = DateRange::wrap($item);
        }

        usort($this->ranges, function (DateRange $a, DateRange $b) {
            return DateRange::compare($a, $b);
        });
    }

    /**
     * @return array
     */
    public function unwrap()
    {
        return array_map(function (DateRange $range) {
            return $range->unwrap();
        }, $this->ranges);
    }

    /**
     * @return DateRange[]
     */
    public function getRanges()
    {
        return $this->ranges;
    }

    /**
     * @param array $ranges
     * @return static
     */
    public function add(array $ranges)
    {
        return new static(
            array_merge(
                $this->ranges,
                $ranges
            )
        );
    }

    /**
     * @param \DateTime|array|DateRange $dateOrRange
     * @return bool
     */
    public function includes($dateOrRange)
    {
        foreach ($this->ranges as $range) {
            if ($range->includes($dateOrRange)) {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @param array|self $coll
     * @return static
     */
    public function intersect($coll)
    {
        $left = $this->ranges;
        $right = static::wrap($coll)->ranges;

        $ranges = [];
        foreach ($left as $leftRange) {
            foreach ($right as $rightRange) {
                $intersection = $leftRange->intersect($rightRange);
                if ($intersection) {
                    $ranges[] = $intersection;
                }
            }
        }

        return (new static($ranges))->join();
    }

    /**
     * @internal
     * @param array|self $collection
     * @return static
     * @throws \InvalidArgumentException
     */
    public static function wrap($collection)
    {
        if ($collection instanceof self) {
            return $collection;
        }

        return new static($collection);
    }

    /**
     * @param array|self $collection
     * @return static
     */
    public function join($collection = NULL)
    {
        $ranges = $this->ranges;

        if ($collection) {
            $ranges = array_merge($ranges, static::wrap($collection)->ranges);
        }

        for ($i = 0; $i < count($ranges); $i++) {
            $a = $ranges[$i];

            foreach (array_slice($ranges, 0) as $j => $b) {
                if ($i === $j) {
                    continue;
                }

                $join = $a->join($b);

                if ($join) {
                    $ranges[$i] = $a = $join;
                    array_splice($ranges, $j, 1);
                }
            }
        }

        return new static(array_filter($ranges));
    }

    /**
     * @param array|self $subtrahends
     * @return static
     */
    public function subtract($subtrahends)
    {
        $minuends = $this->ranges;
        $subtrahends = static::wrap($subtrahends)->ranges;

        foreach ($subtrahends as $subtrahend) {
            $offset = 0;
            /** @var DateRange $minuend */
            foreach (array_slice($minuends, 0) as $i => $minuend) {
                $differences = $minuend->subtract($subtrahend);
                array_splice($minuends, $offset + $i, 1, $differences->ranges);
                $offset += count($differences->ranges) - 1;
            }
        }

        return new static($minuends);
    }
}
