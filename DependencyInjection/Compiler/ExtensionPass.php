<?php

namespace EB\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class ExtensionPass
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class ExtensionPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->has('translator')) {
            $container->getDefinition('twig.extension.trans')->addTag('eb_email.twig.extension');
        }

        if ($container->has('router')) {
            $container->getDefinition('twig.extension.routing')->addTag('eb_email.twig.extension');
        }

        if ($container->has('fragment.handler')) {
            $container->getDefinition('twig.extension.httpkernel')->addTag('eb_email.twig.extension');
        }
    }
}
