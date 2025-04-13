# Upgrading from one version to the other

## 2.0 => 3.0

### Change of signature of AggregateRoot::fromStream() [#55](https://github.com/yvoyer/domain-event/issues/55)

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
