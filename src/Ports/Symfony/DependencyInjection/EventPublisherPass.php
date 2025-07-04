<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Ports\Symfony\SymfonyPublisher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class EventPublisherPass implements CompilerPassInterface
{
    public function __construct(
        private string $event_dispatcher = 'event_dispatcher',
    ) {
    }

    public function process(ContainerBuilder $container): void
    {
        $definition = new Definition(SymfonyPublisher::class, [new Reference($this->event_dispatcher)]);
        foreach ($container->findTaggedServiceIds('star.event_listener') as $serviceId => $tags) {
            $definition->addMethodCall('subscribe', [new Reference($serviceId)]);
        }

        $container->setDefinition('star.event_publisher_default', $definition);
        $container->setAlias('star.event_publisher', 'star.event_publisher_default');
        $container->setAlias(EventPublisher::class, 'star.event_publisher');
    }
}
