<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Stub;

final class DoSomethingHandler
{
    /**
     * @var EventStoreStub
     */
    private $store;

    public function __construct(EventStoreStub $store)
    {
        $this->store = $store;
    }

    public function __invoke(DoSomething $command): void
    {
        $this->store->save($command->action());
    }
}
