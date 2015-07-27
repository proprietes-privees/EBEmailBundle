<?php

namespace EB\EmailBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class EBEmailExtension
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class EBEmailExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $conf = $this->processConfiguration(new Configuration(), $configs);

        // Prepare senders
        $senders = [];
        foreach ($conf['senders'] as $sender) {
            $senders[$sender['email']] = $sender['name'];
        }
        $container->setParameter('eb_email.mailer.mailer.senders', $senders);

        // Merge templates with global vars
        $emails = [];
        foreach ($conf['emails'] as $name => $template) {
            // Receivers
            $receivers = [];
            foreach ($conf['receivers'] as $receiver) {
                $receivers[$receiver['email']] = $receiver['name'];
            }

            // Register
            $emails[$name] = [
                'template' => $template['template'],
                'subject' => $template['subject'],
                'globals' => array_merge(
                    isset($template['globals']) ? $template['globals'] : [],
                    isset($conf['globals']) ? $conf['globals'] : []
                ),
                'images' => array_merge(
                    isset($template['images']) ? $template['images'] : [],
                    isset($conf['images']) ? $conf['images'] : []
                ),
                'attachments' => array_merge(
                    isset($template['attachments']) ? $template['attachments'] : [],
                    isset($conf['attachments']) ? $conf['attachments'] : []
                ),
                'receivers' => $receivers,
            ];

            // Ensure images has string keys
            $keys = array_keys($emails[$name]['images']);
            foreach ($keys as $key) {
                if (is_numeric($key)) {
                    throw new \Exception('Images elements must have a string key');
                }
            }
        }
        $container->setParameter('eb_email.mailer.mailer.emails', $emails);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('mailer.xml');
    }
}
