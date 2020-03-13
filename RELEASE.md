# Release notes

# 3.0.0

* Update composer dependencies
* Add support for symfony event dispatcher api >-5.0

# 2.0.0

* Upgrade/php version bump (**BC break**).
* Add compiler pass to use in symfony application (**BC break**).
* Add message buses utility

**Note**: This release introduce the following **BC breaks**:

* The namespace for classes in `Star\Component\DomainEvent\Symfony` was changed to `Star\Component\DomainEvent\Ports\Symfony`.

# 1.0.1

* Fix #4 - Mutating to another state inside a mutation do not keep the order of events

# 1.0

* Added AggregateRoot

