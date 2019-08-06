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

## Listening to an event

When you wish to perform an operation after an event was dispatched by the `EventPublisher`, you need to define your listener:
 
```php
class DoSomethingProductCreated implements EventListener
{
    // methods on listener can be anything, it is configured by listensTo
    public function doSomething(ProductWasCreated $event): void
    {
        // do something with the event
    }

    public function listensTo(): array
    {
        return [
            ProductWasCreated::class => 'doSomething',
        ];
    }
}
```

The listener needs to be given to the publisher, so that he can send the event.

```php
$publisher = new SymfonyPublisher(new EventDispatcher());
$publisher->subscribe(new DoSomethingProductCreated()); // This is a subscriber that listens to the ProductWasCreated event

$product = Product::create('lightsaber');
$publisher->publishChanges($product->uncommitedEvents()); // will notify the listener and call the DoSomethingProductCreated::doSomething() method
```

**Warning**: Be advised that events will be removed from aggregate once collected, to avoid republishing the same event twice.

We currently support [third party](/docs/ports.md) adapters to allow you to plug-in the library into your infrastructure.

## Naming standard

The events method on the `AggregateRoot` children must be prefixed with `on` and followed by
the event name. ie. For an event class named `StuffWasDone` the aggregate should have a method:

```php
protected function onStuffWasDone(StuffWasDone $event): void;
```

Note: The callback method can be changed to another format, by overriding the `AggregateRoot::getEventMethod()`.

# Message bus

The package adds the ability to dispatch messages (`Command` and `Query`). Compared to the `EventPubliser`, the
 `CommandBus` and `QueryBus` have different usages.
 
* Command bus: Responsible to dispatch an operation that returns nothing. 
* Query bus: Responsible to fetch some information. The returned information is recommended to be returned in a readonly format.

([Example of usage](/examples/Blog/Application/Http/Controller/PostController.php))
 
# Example

The [blog](/examples/blog.phpt) example shows a use case for a blog application.
