<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CommandBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $definition = new Definition(MessageMapBus::class);
        foreach ($container->findTaggedServiceIds('star.command_handler') as $serviceId => $tags) {
            $handlerDefinition = $container->getDefinition($serviceId);
            $message = (string) $handlerDefinition->getClass();
            $class = \substr($message, 0, (int) \strrpos($message, 'Handler'));
            if (! \is_subclass_of($class, Command::class)) {
                throw new \RuntimeException(
                    \sprintf(
                        'The command handler "%s" must have a "Handler" suffix and a command matching the '
                        . 'handler name without the suffix.',
                        $message
                    )
                );
            }

            $definition->addMethodCall(
                'registerHandler',
                [
                    (string) $class,
                    new Reference($serviceId),
                ]
            );
        };

        $container->setDefinition('star.command_bus_default', $definition);
        $container->setAlias('star.command_bus', 'star.command_bus_default');
        $container->setAlias(CommandBus::class, 'star.command_bus');
    }
}
