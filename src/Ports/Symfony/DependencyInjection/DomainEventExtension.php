<?php declare(strict_types=1);

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\EventPublisher;
use Star\Component\DomainEvent\Ports\Logging\LoggableCommandBus;
use Star\Component\DomainEvent\Ports\Logging\LoggablePublisher;
use Star\Component\DomainEvent\Ports\Logging\LoggableQueryBus;
use Star\Component\DomainEvent\Ports\Symfony\SymfonyPublisher;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class DomainEventExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);
        $container
            ->register($concretePublisher = SymfonyPublisher::class)
            ->setArguments([new Reference($config['dispatcher_id'])]);

        if (isset($config['logging'])) {
            $loggingConfig = $config['logging'];
            $container
                ->register($concretePublisher = LoggablePublisher::class)
                ->setArguments(
                    [
                        new Reference($loggingConfig['logger_id']),
                        new Reference($loggingConfig['publisher_id']),
                    ]
                );
            $container
                ->register(LoggableCommandBus::class, LoggableCommandBus::class)
                ->setArgument(0, new Reference($loggingConfig['logger_id']))
            ;
            $container
                ->register(LoggableQueryBus::class, LoggableQueryBus::class)
                ->setArgument(0, new Reference($loggingConfig['logger_id']))
            ;
        }

        $container->setAlias('star.event_publisher', $concretePublisher);
        $container->setAlias(EventPublisher::class, 'star.event_publisher');
    }
}
