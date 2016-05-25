# domain-event

[![Build Status](https://travis-ci.org/yvoyer/domain-event.svg)](https://travis-ci.org/yvoyer/domain-event)

Small implementation of domain events in aggregate root to implement event sourcing.
The aggregate implementation triggers events that can be collected for publishing
by an implementation of `EventPublisher`.

## Installation

* Add the package using [composer](https://getcomposer.org/) in your `composer.json`

```
"require": {
    "star/domain-event": "~1.0"
}
```

## Usage

* Make your class inherit the `AggregateRoot` class.

```php
// Aggregate
class Product extends AggregateRoot
{
    public static function create($name)
    {
        return new self([new ProductWasCreated($name)]);
    }


    // Define methods named according to the convention for each events.
    protected function onProductWasCreated(ProductWasCreated $event)
    {
        $this->name = $event->name();
    }
}

class ProductWasCreated implement DomainEvent
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function name()
    {
        return $this->name;
    }
}

class DoSomethingProductCreated implements EventPublisher
{
    // methods on listener can be anything, it is configured by listensTo
    public function doSomething(ProductWasCreated $event)
    {
        // do something with the event
    }

    public function listensTo()
    {
        return [
            ProductWasCreated::class => 'doSomething',
        ];
    }
}
```

```php
$publisher = new SymfonyPublisher(new EventDispatcher());
$publisher->subscribe(new DoSomethingProductCreated()); // This is a subscriber that listens to the ProductWasCreated event

$product = Product::create('lightsaber');

// be advised that events will be removed from aggregate once collected, to avoid republishing the same event twice
$publisher->publishChanges($product->uncommitedEvents()); // will notify the listener and call the DoSomethingProductCreated::doSomething() method
```

## Naming standard

The events method on `AggregateRoot` children must be prefixed with `on` and followed by
the event name. ie. For an event class named `StuffWasDone` the aggregate should have a method
`protected function onStuffWasDone(StuffWasDone $event);`.
