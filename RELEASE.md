# Release notes

# 2.6.0

* [#65](https://github.com/yvoyer/domain-event/pull/65) Deprecate #48 - Replace usage of payload array in favor of Payload class
* [#64](https://github.com/yvoyer/domain-event/pull/64) Deprecate #31 - Switch default payload definition of array to json
* [#63](https://github.com/yvoyer/domain-event/pull/63) Fix #49 - Deprecate EventListener::listensTo()
* [#61](https://github.com/yvoyer/domain-event/pull/61) Deprecate #50 - Query classes will be removed
* [#60](https://github.com/yvoyer/domain-event/pull/60) Deprecate #47 - Deprecation of construct of SymfonyPublisher
* [#59](https://github.com/yvoyer/domain-event/pull/59) Fix #52 - Allow passing events using splat operator in publish
* [#58](https://github.com/yvoyer/domain-event/pull/58) Deprecate #54 automatic table creation
* [#57](https://github.com/yvoyer/domain-event/pull/57) Fix #55 - Allow passing events using the splat operator.
* [#56](https://github.com/yvoyer/domain-event/pull/56) Fix #51 - Allow mutate to receive more than one event
* [#53](https://github.com/yvoyer/domain-event/pull/53) Add documentation for new features
* [#44](https://github.com/yvoyer/domain-event/pull/44) Update actions versions
* [#43](https://github.com/yvoyer/domain-event/pull/43) Add support to push additional columns in event table
* [#42](https://github.com/yvoyer/domain-event/pull/42) Fix #41 - Add support for DateTime in payload
* [#40](https://github.com/yvoyer/domain-event/pull/40) Add build tests for 8.3

# 2.5.1

* [#35](https://github.com/yvoyer/domain-event/pull/35) Allow override of pushed_on value based on payload

# 2.5.0

* [#33](https://github.com/yvoyer/domain-event/pull/33) Add priority of listeners

# 2.4.1

* [#29](https://github.com/yvoyer/domain-event/pull/29) Add support for ordering on pushed_on column

# 2.4.0

* [#28](https://github.com/yvoyer/domain-event/pull/28) Add Payload object to event reconstruction

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
