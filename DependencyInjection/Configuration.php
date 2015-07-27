<?php

namespace EB\EmailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $tb = new TreeBuilder();
        $children = $tb->root('eb_email')->children();

        /** @var ArrayNodeDefinition $sendersNode */
        $sendersNode = $children
            ->arrayNode('senders')
            ->requiresAtLeastOneElement()
            ->isRequired()
            ->cannotBeEmpty();

        /** @var ArrayNodeDefinition $sendersPrototype */
        $sendersPrototype = $sendersNode->prototype('array');
        $senders = $sendersPrototype->children();
        $senders->scalarNode('name')->isRequired()->cannotBeEmpty()->info('Sender name')->example('John Doe');
        $senders->scalarNode('email')->isRequired()->cannotBeEmpty()->info('Sender email')->example('john.doe@gmail.com');

        // Resources to add for all emails
        $children->arrayNode('globals')->useAttributeAsKey('name')->info('Global to add in each template')->example('A parameter')->prototype('scalar');
        $children->arrayNode('images')->useAttributeAsKey('name')->info('Images to attach inline')->example('/path/to/file')->prototype('scalar');
        $children->arrayNode('attachments')->info('Files to attach')->example('/path/to/file')->prototype('scalar');

        /** @var ArrayNodeDefinition $templateNode */
        $templateNode = $children
            ->arrayNode('emails')
            ->useAttributeAsKey('name');

        /** @var ArrayNodeDefinition $templatePrototype */
        $templatePrototype = $templateNode->prototype('array');
        $template = $templatePrototype->children();
        $template->scalarNode('template')->isRequired()->cannotBeEmpty()->info('Template')->example('AcmeDefautBundle::email.html.twig');
        $template->scalarNode('subject')->isRequired()->cannotBeEmpty()->info('Email subject (twig template)')->example('Welcome {{user.username}} to {{app_name}} !');
        $template->arrayNode('globals')->useAttributeAsKey('name')->info('Global to add in this template')->example('A parameter')->prototype('scalar');
        $template->arrayNode('images')->useAttributeAsKey('name')->info('Images to attach inline')->example('/path/to/file')->prototype('scalar');
        $template->arrayNode('attachments')->info('Files to attach')->example('/path/to/file')->prototype('scalar');

        // Static receivers
        $receiver = $children->arrayNode('receivers')->addDefaultsIfNotSet()->children();
        $receiver->scalarNode('name')->isRequired()->cannotBeEmpty()->info('Receiver name')->example('John Doe');
        $receiver->scalarNode('email')->isRequired()->cannotBeEmpty()->info('Receiver email')->example('john.doe@gmail.com');

        return $tb;
    }
}
