<?php

namespace EB\EmailBundle;

use EB\EmailBundle\DependencyInjection\Compiler\ExtensionPass;
use EB\EmailBundle\DependencyInjection\Compiler\TwigExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EBEmailBundle
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class EBEmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ExtensionPass());
        $container->addCompilerPass(new TwigExtensionPass());
    }
}
