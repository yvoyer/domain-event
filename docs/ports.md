# Ports

This package currently support the following third party libs or framework:

* [Symfony framework](/src/Ports/Symfony/README.md)

Note: Any new implementation is welcome. Go submit a PR. 

## Doctrine DBAL

```php
use Star\Component\DomainEvent\Ports\Doctrine\DBALEventStore;

final MyEventStore extends DBALEventStore 
{
    protected function tableName(): string
    {
        return '_my_events';
    }

    protected function createAggregateFromStream(array $events): AggregateRoot
    {
        return MyAggregateImplementation::fromStream($events);
    }

    protected function handleNoEventFound(string $id): void
    {
        throw new RuntimeException(\sprintf('My aggregate "%s" not found.', $id));
    }

    public function loadAggregate(string $id): MyAggregateImplementation
    {
        return $this->getAggregateWithId($id);
    }

    public function saveAggregate(MyAggregateImplementation $aggregate): void
    {
        $this->persistAggregate($post->getId(), $aggregate);
    }
    
    protected function getOrderColumns(): array
    {
        // Optionally you may override this method to change the column to use for ordering events. (Default = "version")
        return ['pushed_on'];
    }
}
```
