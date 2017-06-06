# DateRange

__Working with date ranges made easy.__

## Usage

Create date range collection:

```
$coll = new \Danoha\DateRangeCollection([
    [ $from, $to ], // two items per range accepted
    [ 'from' => $from, 'to' => $to, ], // accepted too
    
    [ $from, NULL, ], // NULL means indefinite interval
    [ NULL, NULL, ], // and can be used on both sides
]);
```

To get your ranges back:

```
$coll->unwrap() === [
    [ 'from' => $from, 'to' => $to, ], // every range has this exact format
    [ 'from' => $from, 'to' => $to, ], // regardless of what was passed to constructor
    ...
]
```

Every method accepts collection or array of ranges:

```
$coll->intersect(
    // another collection
    new \Danoha\DateRangeCollection([ ... ])
);

$coll->intersect([
    // inlined collection (same as constructor)
    [ 'from' => $from, 'to' => $to, ]
]);
```

Note: definite intervals are handled as inclusive on both sides.

## Available methods

Note: all methods returning collection return new instance.
That means calling `$coll->add($range)` twice on the same
collection will create two instances on neither of them will
contain both added ranges.

- unwrap - get underlying date ranges in common format,
- add - add given ranges to collection,
- includes - tests if collection includes given date,
- join - joins ranges in current collection if possible,
- intersect - calculates all intersections with given ranges.