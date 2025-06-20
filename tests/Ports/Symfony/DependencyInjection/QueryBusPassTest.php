<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use function get_class;
use function sprintf;

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
        self::assertSame('result', $controller->searchStuff());
    }

    public function test_it_should_throw_exception_when_handler_is_missing_handler_suffix(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', 'InvalidClass')
            ->addTag('star.query_handler')
        ;
        $builder->addCompilerPass(new QueryBusPass());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The query handler "InvalidClass" must have a "Handler" suffix.');
        $builder->compile();
    }

    public function test_it_should_throw_exception_when_query_do_not_implement_interface(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', 'DateTimeHandler')
            ->addTag('star.query_handler')
        ;
        $builder->addCompilerPass(new QueryBusPass());

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(
            sprintf('The query "DateTime" must implement the "%s" interface.', Query::class)
        );
        $builder->compile();
    }

    public function test_it_should_allow_custom_class_message(): void
    {
        $query = new class implements QueryWithResult {
            private string $result;

            /**
             * @param string $result
             */
            public function __invoke($result): void
            {
                $this->result = $result;
            }

            public function getResult(): string
            {
                return $this->result;
            }
        };
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', SearchStuffHandler::class)
            ->addTag('star.query_handler', ['message' => get_class($query)])
        ;
        $builder->addCompilerPass(new QueryBusPass());
        $builder->compile();

        /**
         * @var QueryController $controller
         */
        $controller = $builder->get(QueryController::class);
        self::assertSame('result', $controller->dispatchQuery($query));
        self::assertSame('result', $query->getResult());
    }
}
interface QueryWithResult extends Query
{
    public function getResult(): string;
}

final class QueryController
{
    public function __construct(
        private QueryBus $queryBus,
    ) {
    }

    public function searchStuff(): string
    {
        return $this->dispatchQuery(new SearchStuff());
    }

    public function dispatchQuery(QueryWithResult $query): string
    {
        $this->queryBus->dispatchQuery($query);

        return $query->getResult();
    }
}
final class SearchStuff implements QueryWithResult
{
    public string $result;

    /**
     * @param string $result
     */
    public function __invoke($result): void
    {
        $this->result = $result;
    }

    public function getResult(): string
    {
        return $this->result;
    }
}
final class SearchStuffHandler
{
    public function __invoke(Query $query): void
    {
        $query('result');
    }
}
