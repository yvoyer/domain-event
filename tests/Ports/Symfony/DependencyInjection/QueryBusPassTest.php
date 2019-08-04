<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class QueryBusPassTest extends TestCase
{
    public function test_it_should_dispatch_query(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', SearchStuffHandler::class)
            ->addTag('star.query_handler')
        ;
        $builder->addCompilerPass(new QueryBusPass());
        $builder->compile();

        /**
         * @var QueryController $controller
         */
        $controller = $builder->get(QueryController::class);
        $this->assertSame('result', $controller->searchStuff());
    }

    public function test_it_should_throw_exception_when_handler_is_not_correct_format(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', WillThrowExceptionForInvalidFormat::class)
            ->addTag('star.query_handler')
        ;
        $builder->addCompilerPass(new QueryBusPass());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            'The query handler "' . WillThrowExceptionForInvalidFormat::class
            . '" must have a "Handler" suffix and a query matching the handler name without the suffix.'
        );
        $builder->compile();
    }
}
final class QueryController
{
    /**
     * @var QueryBus
     */
    private $queryBus;

    public function __construct(QueryBus $queryBus)
    {
        $this->queryBus = $queryBus;
    }

    public function searchStuff(): string
    {
        return $this->dispatchQuery(new SearchStuff());
    }

    public function dispatchQuery(Query $query)
    {
        $this->queryBus->dispatchQuery($query);

        return $query->getResult();
    }
}
final class SearchStuff implements Query
{
    private $result;

    public function __invoke($result): void
    {
        $this->result = $result;
    }

    public function getResult()
    {
        return $this->result;
    }
}
final class SearchStuffHandler
{
    public function __invoke(SearchStuff $query): void
    {
        $query('result');
    }
}
final class WillThrowExceptionForInvalidFormat implements Query {
    public function __invoke($result): void
    {
        throw new \RuntimeException('Method ' . __METHOD__ . ' not implemented yet.');
    }

    public function getResult()
    {
        throw new \RuntimeException('Method ' . __METHOD__ . ' not implemented yet.');
    }
}
