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
        $senders = array();
        foreach ($conf['senders'] as $sender) {
            $senders[$sender['email']] = $sender['name'];
        }
        $container->setParameter('eb_email.mailer.mailer.senders', $senders);

        // Merge templates with global vars
        $emails = array();
        foreach ($conf['emails'] as $name => $template) {
            // @todo
            // The template must exist
            // if (false === $this->templating->exists($template['template'])) {
            //     throw new \Exception(sprintf('This template does not exist "%s"', $template['template']));
            // }

            // Receivers
            $receivers = array();
            foreach ($conf['receivers'] as $receiver) {
                $receivers[$receiver['email']] = $receiver['name'];
            }

            // Register
            $emails[$name] = array(
                'template' => $template['template'],
                'subject' => $template['subject'],
                'images' => array_merge(
                    isset($template['images']) ? $template['images'] : array(),
                    isset($conf['images']) ? $conf['images'] : array()
                ),
                'attachments' => array_merge(
                    isset($template['attachments']) ? $template['attachments'] : array(),
                    isset($conf['attachments']) ? $conf['attachments'] : array()
                ),
                'receivers' => $receivers,
            );

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
        $loader->load('services.xml');
    }
}
