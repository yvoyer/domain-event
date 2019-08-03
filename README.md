# domain-event

[![Build Status](https://travis-ci.org/yvoyer/domain-event.svg)](https://travis-ci.org/yvoyer/domain-event)

Small implementation of the aggregate root in [ddd](https://en.wikipedia.org/wiki/Domain-driven_design). 
The `AggregateRoot` implementation triggers events that can be collected for publishing by an implementation of `EventPublisher`.

## Installation

* Add the package using [composer](https://getcomposer.org/) in your `composer.json`

`composer require star/domain-event`

## Usage

1. Make your class inherit the `AggregateRoot` class.

```php
// Product.php
class Product extends AggregateRoot
{
}
```

2. Create the event for the mutation.

```php
class ProductWasCreated implements DomainEvent
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }
}
```

3. Create a named constructor (static method), or a mutation method.

```php
// Product.php
    /**
     * Static construct, since the __construct() is protected
     *
     * @param string $name
     * @return Product
     */
    public static function draftProduct(string $name): Product
    {
        return new self([new ProductWasCreated($name)]);
    }
    
    /**
     * Mutation method that handles the business logic of your aggregate
     */
    public function confirm(): void
    {
        $this->mutate(new ProductWasConfirmed($this->getId()));
    }
```

4. Create the [callback](#naming-standard) method on the `AggregateRoot` that would be used to set the state after an event mutation.

```php
    protected function onProductWasCreated(ProductWasCreated $event): void
    {
        $this->name = $event->name();
    }
}
```

```php
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

## Listening to an event

```php
$publisher = new SymfonyPublisher(new EventDispatcher());
$publisher->subscribe(new DoSomethingProductCreated()); // This is a subscriber that listens to the ProductWasCreated event

$product = Product::create('lightsaber');

// be advised that events will be removed from aggregate once collected, to avoid republishing the same event twice
$publisher->publishChanges($product->uncommitedEvents()); // will notify the listener and call the DoSomethingProductCreated::doSomething() method
```

## Naming standard

The events method on `AggregateRoot` children must be prefixed with `on` and followed by
the event name. ie. For an event class named `StuffWasDone` the aggregate should have a method:

```php
protected function onStuffWasDone(StuffWasDone $event);
```

Note: The callback method can be changed to another format, by overriding the `AggregateRoot::getEventMethod()`.

