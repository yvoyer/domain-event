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

### Remove support for old dispatcher interface in [SymfonyPublisher](src/Ports/Symfony/SymfonyPublisher.php)

[See #18](https://github.com/yvoyer/domain-event/issues/18)
[See #47](https://github.com/yvoyer/domain-event/issues/47)
[See #52](https://github.com/yvoyer/domain-event/issues/52)

*What to do:*

You must stop injecting instance of type `Symfony\Component\EventDispatcher\EventDispatcherInterface` to
 the `SymfonyPublisher`. Starting in 3.0, we'll only be allowing instance of type
 `Symfony\Contracts\EventDispatcher\EventDispatcherInterface`.

### Remove support of Query classes

[See #50](https://github.com/yvoyer/domain-event/issues/50)

*What to do:*

You only need to change from `extends` to `implements Query`.
 You may also need to remove your definition of `validateResult()`, since it won't be called anymore,
 unless you want to validate it your way. 

```php
// before
class YourCollection extends CollectionQuery { ... }
class YourScalar extends ScalarQuery { ... }
class YourObject extends ObjectQuery { ... }

// after
class YourCollection implements Query { ... }
class YourScalar implements Query { ... }
class YourObject implements Query { ... }
```
