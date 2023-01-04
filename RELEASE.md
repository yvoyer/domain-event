# Release notes

# 2.3.2

* [#26](https://github.com/yvoyer/domain-event/pull/26) Add new collection method

# 2.3.1

* [#25](https://github.com/yvoyer/domain-event/pull/25) Remove final keyword from private method

# 2.3.0

* [#22](https://github.com/yvoyer/domain-event/pull/22) Add configurable mapping for query and command
* [#24](https://github.com/yvoyer/domain-event/pull/24) Add in-memory event store

# 2.2.0

* [#20](https://github.com/yvoyer/domain-event/pull/20) Add DBAL event store

# 2.1.1

* Fix bad order for `dispatch()` method see #19 

# 2.1.0

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

