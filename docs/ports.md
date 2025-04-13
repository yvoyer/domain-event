# Ports

This package currently support the following third party libs or framework:

* [Symfony framework](/src/Ports/Symfony/README.md)

Note: Any new implementation is welcome. Go submit a PR. 

## Doctrine DBAL

```php
use Doctrine\DBAL\Types\Types;use Star\Component\DomainEvent\Ports\Doctrine\DBALEventStore;use Star\Component\DomainEvent\Ports\Doctrine\RowDatasetBuilder;use Star\Component\DomainEvent\Ports\Event\AfterEventPersist;use Star\Component\DomainEvent\Ports\Event\BeforeEventPersist;use Star\Component\DomainEvent\Serialization\Payload;

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

    /**
     * Defines which columns to use when fetching the events of the aggregate. 
     * 
     * @return array<int, string>
     * @see DBALEventStore::getAggregateWithId()    
     */    
    protected function getOrderColumns(): array
    {
        return [
            self::COLUMN_PUSHED_ON,
            self::COLUMN_VERSION,
        ];
    }
    
    protected function createPushedOnDateFromPayload(array $payload): DateTimeImmutable
    {
        // This date will be used as the "pushed_on" date for the current store. 
        return new DateTimeImmutable($payload['completed_at']);
    }
    
    /**
     * Allows you to change the row data before it is added in the DB. 
     * @see BeforeEventPersist The event allows you to get the same information in another service.
     */
    protected function beforeEventPersist(
        string $id,
        string $eventName,
        Payload $payload,
        RowDatasetBuilder $builder,
        DateTimeInterface $pushedOn,
    ): RowDatasetBuilder {
        if ($payload->keyExists('some_key')) {
            $builder->setIntegerColumn(
                'custom_attribute', // This column will be added to the row 
                $payload->getInteger('some_key'),
                Types::BIGINT, // you may define which type to use
            );
        }
        
        return parent::beforeEventPersist(
            $id,
            $eventName,
            $payload,
            $builder,
            $pushedOn,
        );
    }
    
    /**
     * Allows you to read the row data after it was added in the DB.
     * @see AfterEventPersist The event allows you to get the same information in another service.  
     */
    protected function afterEventPersist(
        string $id,
        string $eventName,
        Payload $payload,
        DateTimeInterface $pushedOn,
    ): void {
        // You can perform some logic inside the store after the row is created.
        
        parent::afterEventPersist($id,$eventName,$payload,$pushedOn);
    }
}
```
