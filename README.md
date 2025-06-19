# domain-event

![Build Status](https://github.com/yvoyer/domain-event/actions/workflows/php.yml/badge.svg)

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
    private string $name;

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

3. Create a named constructor (static method), and a mutation method. The protected method will be invoked internally 
 using the [naming standard](#naming-standard) of mutation methods. 

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
        return self::fromStream([new ProductWasCreated($name)]);
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
    // methods on listener can be anything, it is configured by getListenedEvents
    public function doSomething(ProductWasCreated $event): void
    {
        // do something with the event
    }

    public function doSomethingAtFirst(PostWasPublished $event): void 
    {
    }

    public function doSomethingInBetween(PostWasPublished $event): void 
    {
    }

    public function doSomethingAtLast(PostWasPublished $event): void 
    {
    }
    
    public static function getListenedEvents(): array
    {
        return [
            ProductWasCreated::class => 'doSomething', // priority will be assigned at runtime
            PostWasPublished::class => [ // multiple methods may be assigned priority
                100 => 'doSomethingAtFirst',
                0 => 'doSomethingInBetween',
                -20 => 'doSomethingAtLast',
            ],
        ];
    }
}
```

The listener needs to be given to the publisher, so that he can send the event.

```php
$publisher = new class() implements EventPublisher {}; // your implementation choice
$publisher->subscribe(new DoSomethingProductCreated()); // This is a subscriber that listens to the ProductWasCreated event

$product = Product::draftProduct('lightsaber');
$publisher->publish(...$product->uncommitedEvents()); // will notify the listener and call the DoSomethingProductCreated::doSomething() method
```

**Warning**: Be advised that events will be removed from aggregate once collected and published,
 to avoid republishing the same event twice.

We currently support [third party](/docs/ports.md) adapters to allow you to plug-in the library into your infrastructure.

## Naming standard

The events method on the `AggregateRoot` children must be prefixed with `on` and followed by
the event name. ie. For an event class named `StuffWasDone` the aggregate should have a method:

```php
protected function onStuffWasDone(StuffWasDone $event): void;
```

Note: The callback method can be changed to another format, by overriding the `AggregateRoot::getEventMethod()`.

```php
protected function getEventMethod(DomainEvent $event): string
{
    if ($event instanceof StuffWasDone) {
        return 'whenYouDoStuff'; // the protected method whenYouDoStuff() would be invoked to apply the change to the aggregate
    }
}
```
## Message bus

The package adds the ability to dispatch messages (`Command` and `Query`). Compared to the `EventPubliser`, the
 `CommandBus` and `QueryBus` have different usages.
 
* Command bus: Responsible to dispatch an operation that returns nothing. 
* Query bus: Responsible to fetch some information. The returned information is recommended to be returned in a readonly format.

([Example of usage](/examples/Blog/Application/Http/Controller/PostController.php))

## Serialization of events

When persisting your event in a data store, you may use a [PayloadSerializer](src/Serialization/PayloadSerializer.php) instance to convert your event to a
 serializable string.
The current implementation [PayloadFromReflection](src/Serialization/PayloadFromReflection.php) allow you to:
* register [PropertyValueTransformer](src/Serialization/Transformation/PropertyValueTransformer.php) to ensure your value objects are converted to a serializable format. 
* Or implement the [SerializableAttribute interface](src/Serialization/SerializableAttribute.php) to contain the logic in each of your value objects.

## Example

The [blog](/examples/blog.phpt) example shows a use case for a blog application.

## Symfony usage

Using a Symfony application, you may use the provided compiler passes to use the buses.

```php

use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\CommandBusPass;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\EventPublisherPass;
use Star\Component\DomainEvent\Ports\Symfony\DependencyInjection\QueryBusPass;

// Kernel.php
public function build(ContainerBuilder $container): void {
    $container->addCompilerPass(new CommandBusPass());
    $container->addCompilerPass(new QueryBusPass());
    $container->addCompilerPass(new EventPublisherPass());
}
```

Once registered, three new tags will be available:

* `star.command_handler`
* `star.query_handler`
* `star.event_publisher`

The tags `star.command_handler` and `star.query_handler` have an optional attribute `message` to specify the
 message FQCN that will be mapped to this handler. By default the system will try to resolve the same FQCN as the
 handler, without the `Handler` suffix.

```
// services.yaml
services:
    Path/For/My/Project/DoStuffHandler:
      tags:
        - { name star.command_handler, message: Path/For/My/Project/DoStuff }

    Path/For/My/Project/FetchStuffHandler:
      tags:
        - { name star.query_handler, message: Path\For\My\Project\FetchStuff }
```
*Note*: In both cases, omitting the message attributes would result in the same behavior.

## Event store

Event stores are where your events will be persisted. You define which platform is used by extending the provided store.

[Example](/docs/ports.md#doctrine-dbal) with `DBALEventStore`.
