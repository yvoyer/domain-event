# Symfony adapter

See [Symfony documentation](https://symfony.com) for more information.

## Compiler pass

You can use the provided `EventPublisherPass` that will manage the registering of the event publisher.
The compiler pass will register a service `star.event_publisher` with an alias `Star\Component\DomainEvent\EventPublisher`.
A tag named `star.event_listener` is also used to mark your services as listeners.

1. Register the [compiler pass](https://symfony.com/doc/current/service_container/compiler_passes.html) to your container.
2. Register your service with the [tag](https://symfony.com/doc/current/service_container/tags.html) `star.event_listener`.
3. You listener should now be invoked when a service calls the `EventPublisher::publish()` method.
