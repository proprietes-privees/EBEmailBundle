<?php

namespace EB\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class TwigExtensionPass
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class TwigExtensionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (false === $container->hasDefinition('eb_email.twig.environment')) {
            return;
        }

        $definition = $container->getDefinition('eb_email.twig.environment');
        $calls = $definition->getMethodCalls();
        $definition->setMethodCalls(array());
        foreach ($container->findTaggedServiceIds('eb_email.twig.extension') as $id => $attributes) {
            $definition->addMethodCall('addExtension', array(new Reference($id)));
        }
        $definition->setMethodCalls(array_merge($definition->getMethodCalls(), $calls));
    }
}
