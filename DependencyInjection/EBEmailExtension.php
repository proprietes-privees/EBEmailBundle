<?php

namespace EB\EmailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
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
        $senders = array_combine(array_map(function (array $sender) {
            return $sender['email'];
        }, $conf['senders']), array_map(function (array $sender) {
            return $sender['name'];
        }, $conf['senders']));

        $container->setParameter('eb_email.mailer.mailer.senders', $senders);

        // Merge templates with global vars
        $emails = [];
        foreach ($conf['emails'] as $name => $template) {
            // Globals
            $globals = array_merge($template['globals'], $conf['globals']);
            $this->ensureArrayIsAssociative($globals, '"globals" configuration in eb_email must be an associative array');

            // Images
            $images = array_merge($template['images'], $conf['images']);
            $this->ensureArrayIsAssociative($images, '"images" configuration in eb_email must be an associative array');
            $this->ensureFilesExist($images);

            // Attachments
            $attachments = array_values(array_merge($template['attachments'], $conf['attachments']));
            $this->ensureFilesExist($images);

            // Recipients
            $recipients = array_merge($template['recipients'], $conf['recipients']);

            // Register this email
            $emails[$name] = [
                'text_template' => $template['text_template'],
                'html_template' => $template['html_template'],
                'subject' => $template['subject'],
                'globals' => $globals,
                'images' => $images,
                'attachments' => $attachments,
                'recipients' => $recipients,
            ];
        }
        $container->setParameter('eb_email.mailer.mailer.emails', $emails);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('mailer.xml');
    }

    /**
     * Ensure array is associative
     *
     * @param array  $array            Array
     * @param string $exceptionMessage Exception message
     */
    private function ensureArrayIsAssociative(array $array, $exceptionMessage)
    {
        foreach ($array as $key => $value) {
            if (!is_string($key)) {
                throw new InvalidConfigurationException($exceptionMessage);
            }
        }
    }

    /**
     * Ensure files exist
     *
     * @param string[] $files
     */
    private function ensureFilesExist(array $files)
    {
        foreach ($files as $file) {
            if (!is_file($file)) {
                throw new InvalidConfigurationException(sprintf(
                    'The file "%s" does not exist in eb_email configuration',
                    $file
                ));
            }
        }
    }
}
