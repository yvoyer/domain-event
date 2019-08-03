# Release notes

# 2.0.0

* Upgrade/php version bump (**BC break**).
* Add compiler pass to use in symfony application (**BC break**).

**Note**: This release introduce the following **BC breaks**:

* The namespace for classes in `Star\Component\DomainEvent\Symfony` was changed to `Star\Component\DomainEvent\Ports\Symfony`.

# 1.0.1

* Fix #4 - Mutating to another state inside a mutation do not keep the order of events

# 1.0

* Added AggregateRoot

