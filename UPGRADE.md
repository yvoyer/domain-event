# Upgrading from one version to the other

## 2.0 => 3.0

### Change of signature of AggregateRoot::fromStream()

[See #55](https://github.com/yvoyer/domain-event/issues/55)

*What to do:*

The `array` support was removed in favor of splat operator. You need to pass your events without the array.

```php
// before
return self::fromStream(
    [
        new YourEvents(),
        new YourEvents(),
        new YourEvents(),
    ]   
);

// Now 
return self::fromStream(
    new YourEvents(),
    new YourEvents(),
    new YourEvents(),
);

```

### Removal of automatic table event table creation in [DBALEventStore](src/Ports/Doctrine/DBALEventStore.php).

[See #54](https://github.com/yvoyer/domain-event/issues/54)

*What to do:* 

You must ensure your table exists on your side when the events tables do not exist. Use migrations or manual script.

:warning: No replacement is provided.
