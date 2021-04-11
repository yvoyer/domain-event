<?php declare(strict_types=1);

/**
 * (c) Yannick Voyer (http://github.com/yvoyer)
 *
 * @since 2.0
 */

namespace Star\Component\DomainEvent\Ports\Symfony\DependencyInjection;

use InvalidArgumentException;
use Star\Component\DomainEvent\Messaging\Command;
use Star\Component\DomainEvent\Messaging\CommandBus;
use Star\Component\DomainEvent\Messaging\MessageMapBus;
use Star\Component\DomainEvent\Ports\Logging\LoggableCommandBus;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CommandBusPass implements CompilerPassInterface
{
    const TAG_NAME = 'star.command_handler';
    const TAG_ATTRIBUTE_MESSAGE = 'message';

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->register(MessageMapBus::class, MessageMapBus::class);
        foreach ($container->findTaggedServiceIds(self::TAG_NAME) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $handlerDefinition = $container->getDefinition($serviceId);
                $handlerClass = (string) $handlerDefinition->getClass();
                $command = (string) \substr($handlerClass, 0, (int) \strrpos($handlerClass, 'Handler'));
                if (\strlen($command) === 0) {
                    throw new InvalidArgumentException(
                        \sprintf('The handler "%s" must have a "Handler" suffix.', $handlerClass)
                    );
                }

                if (isset($tag[self::TAG_ATTRIBUTE_MESSAGE])) {
                    $command = (string) $tag[self::TAG_ATTRIBUTE_MESSAGE];
                }

                if (! \is_subclass_of($command, Command::class)) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            'The command "%s" must be a class implementing interface "%s".',
                            $command,
                            Command::class
                        )
                    );
                }

                $definition->addMethodCall(
                    'registerHandler',
                    [
                        $command,
                        new Reference($serviceId),
                    ]
                );
            }
        };

        if ($container->hasDefinition(LoggableCommandBus::class)) {
            $definition = $container->getDefinition(LoggableCommandBus::class);
        }

        $container->setDefinition('star.command_bus_default', $definition);
        $container->setAlias('star.command_bus', 'star.command_bus_default');
        $container->setAlias(CommandBus::class, 'star.command_bus');
    }
}
