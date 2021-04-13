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

        $this->expectException(\InvalidArgumentException::class);
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

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(
            \sprintf('The query "DateTime" must implement the "%s" interface.', Query::class)
        );
        $builder->compile();
    }

    public function test_it_should_throw_exception_when_query_class_do_not_exists(): void
    {
        $builder = new ContainerBuilder();
        $builder->register(QueryController::class, QueryController::class)
            ->addArgument(new Reference('star.query_bus'))
            ->setPublic(true);
        $builder
            ->register('my_handler', 'WhateverHandler')
            ->addTag('star.query_handler')
        ;
        $builder->addCompilerPass(new QueryBusPass());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('The query "Whatever" do not exists.');
        $builder->compile();
    }

    public function test_it_should_allow_custom_class_message(): void
    {
        $query = new class implements Query {
            private $result;

            public function __invoke($result): void
            {
                $this->result = $result;
            }

            public function getResult()
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
            ->addTag('star.query_handler', ['message' => \get_class($query)])
        ;
        $builder->addCompilerPass(new QueryBusPass());
        $builder->compile();

        /**
         * @var QueryController $controller
         */
        $controller = $builder->get(QueryController::class);
        $controller->dispatchQuery($query);
        self::assertSame('result', $query->getResult());
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

    public function dispatchQuery($query)
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
    public function __invoke($query): void
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
