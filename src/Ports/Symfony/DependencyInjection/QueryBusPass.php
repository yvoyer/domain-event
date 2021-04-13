<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use InvalidArgumentException;
use RuntimeException;
use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Star\Component\DomainEvent\Messaging\Query;
use Star\Component\DomainEvent\Messaging\QueryBus;
use Star\Component\DomainEvent\Ports\Logging\LoggableQueryBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use function class_exists;
use function is_subclass_of;
use function sprintf;
use function strlen;
use function strrpos;
use function substr;

final class QueryBusPass implements CompilerPassInterface
{
    const TAG_NAME = 'star.query_handler';
    const ATTRIBUTE_MESSAGE = 'message';

    public function process(ContainerBuilder $container): void
    {
        $definition = new Definition(MessageMapBus::class);
        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $handlerDefinition = $container->getDefinition($serviceId);
                $handlerClass = (string) $handlerDefinition->getClass();
                $queryClass = substr($handlerClass, 0, (int) strrpos($handlerClass, 'Handler'));
                if (strlen($queryClass) === 0) {
                    throw new InvalidArgumentException(
                        sprintf('The query handler "%s" must have a "Handler" suffix.', $handlerClass)
                    );
                }

                if (isset($tag[self::ATTRIBUTE_MESSAGE])) {
                    $queryClass = $tag[self::ATTRIBUTE_MESSAGE];
                }

                if (! class_exists($queryClass)) {
                    throw new RuntimeException(
                        sprintf(
                            'The query "%s" do not exists. Did you may define the attribute "message" '
                            . 'in the tag or use the same namespace than the handler, without the "Handler" suffix?',
                            $queryClass
                        )
                    );
                }

                if (! is_subclass_of($queryClass, Query::class)) {
                    throw new RuntimeException(
                        sprintf(
                            'The query "%s" must implement the "%s" interface.',
                            $queryClass,
                            Query::class
                        )
                    );
                }

                $definition->addMethodCall(
                    'registerHandler',
                    [
                        $queryClass,
                        new Reference($serviceId),
                    ]
                );
            }
        };

        if ($container->hasDefinition(LoggableQueryBus::class)) {
            $definition = $container
                ->getDefinition(LoggableQueryBus::class)
                ->setArgument(1, $definition);
        }

        $container->setDefinition('star.query_bus_default', $definition);
        $container->setAlias('star.query_bus', 'star.query_bus_default');
        $container->setAlias(QueryBus::class, 'star.query_bus');
    }
}
