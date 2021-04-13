<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Ports\Logging\LoggablePublisher;
use Star\Component\DomainEvent\Ports\Symfony\SymfonyPublisher;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class EventPublisherPass implements CompilerPassInterface
{
    /**
     * The name of the tag
     */
    const TAG_NAME = 'star.event_listener';

    /**
     * @var string
     */
    private $event_dispatcher;

    public function __construct(string $event_dispatcher = 'event_dispatcher')
    {
        $this->event_dispatcher = $event_dispatcher;
    }

    public function process(ContainerBuilder $container): void
    {
        $isLoadedByExtension = $container->hasAlias('star.event_publisher');
        if (! $isLoadedByExtension) {
            $definition = $container
                ->register(SymfonyPublisher::class, SymfonyPublisher::class)
                ->setArguments([new Reference($this->event_dispatcher)]);
            $container->setDefinition('star.event_publisher_default', $definition);
            $container->setAlias('star.event_publisher', 'star.event_publisher_default');
            $container->setAlias(EventPublisher::class, 'star.event_publisher');
        }

        $definition = $container->getDefinition(SymfonyPublisher::class);
        if ($container->hasDefinition(LoggablePublisher::class)) {
            $definition = $container->getDefinition(LoggablePublisher::class);
        }

        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $tags) {
            $definition->addMethodCall('subscribe', [new Reference($serviceId)]);
        }
    }
}
