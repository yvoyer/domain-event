<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class QueryBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(MessageMapBus::class);
        foreach ($container->findTaggedServiceIds('star.query_handler') as $serviceId => $tags) {
            $handlerDefinition = $container->getDefinition($serviceId);
            $message = (string) $handlerDefinition->getClass();
            $class = \substr($message, 0, (int) \strrpos($message, 'Handler'));
            if (! \is_subclass_of($class, Query::class)) {
                throw new \RuntimeException(
                    \sprintf(
                        'The query handler "%s" must have a "Handler" suffix and a query matching '
                        . 'the handler name without the suffix.',
                        $message
                    )
                );
            }

            $definition->addMethodCall(
                'registerHandler',
                [
                    $class,
                    new Reference($serviceId),
                ]
            );
        };

        $container->setDefinition('star.query_bus_default', $definition);
        $container->setAlias('star.query_bus', 'star.query_bus_default');
        $container->setAlias(QueryBus::class, 'star.query_bus');
    }
}
