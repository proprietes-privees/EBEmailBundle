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

        // Static recipients
        $recipients = $children->arrayNode('recipients')->addDefaultsIfNotSet()->children();
        $recipients->scalarNode('name')->isRequired()->cannotBeEmpty()->info('Recipient name')->example('John Doe');
        $recipients->scalarNode('email')->isRequired()->cannotBeEmpty()->info('Recipient email')->example('john.doe@gmail.com');

        /** @var ArrayNodeDefinition $emailsNode */
        $emailsNode = $children
            ->arrayNode('emails')
            ->useAttributeAsKey('name');

        /** @var ArrayNodeDefinition $emailsPrototype */
        $emailsPrototype = $emailsNode->prototype('array');
        $email = $emailsPrototype->children();
        $email->scalarNode('text_template')->isRequired()->cannotBeEmpty()->info('Text Template')->example('AcmeDefautBundle::_text_email.html.twig');
        $email->scalarNode('html_template')->defaultNull()->info('HTML Template')->example('AcmeDefautBundle::_html_email.html.twig');
        $email->scalarNode('subject')->isRequired()->cannotBeEmpty()->info('Email subject')->example('Welcome {{user.username}} to {{app_name}} !');
        $email->arrayNode('globals')->useAttributeAsKey('name')->info('Global to add in this template')->example('A parameter')->prototype('scalar');
        $email->arrayNode('images')->useAttributeAsKey('name')->info('Images to attach inline')->example('/path/to/file')->prototype('scalar');
        $email->arrayNode('attachments')->info('Files to attach')->example('/path/to/file')->prototype('scalar');

        // Static email recipients
        $emailRecipients = $email->arrayNode('recipients')->addDefaultsIfNotSet()->children();
        $emailRecipients->scalarNode('name')->isRequired()->cannotBeEmpty()->info('Recipient name')->example('John Doe');
        $emailRecipients->scalarNode('email')->isRequired()->cannotBeEmpty()->info('Recipient email')->example('john.doe@gmail.com');

        return $tb;
    }
}
