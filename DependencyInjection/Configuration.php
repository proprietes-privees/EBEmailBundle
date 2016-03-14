<?php

namespace EB\EmailBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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

        // Resources to add to all emails
        $this->addGlobalsChildrenConfiguration($children);
        $this->addImagesChildrenConfiguration($children);
        $this->addAttachmentsChildrenConfiguration($children);
        $this->addRecipientsChildrenConfiguration($children);

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
        $this->addGlobalsChildrenConfiguration($email);
        $this->addImagesChildrenConfiguration($email);
        $this->addAttachmentsChildrenConfiguration($email);
        $this->addRecipientsChildrenConfiguration($email);

        return $tb;
    }

    /**
     * Add globals children configuration
     *
     * @param NodeBuilder $children
     */
    private function addGlobalsChildrenConfiguration(NodeBuilder $children)
    {
        $children
            ->arrayNode('globals')
            ->useAttributeAsKey('name')
            ->info('Global to add in this template')
            ->example('A parameter')
            ->prototype('scalar');
    }

    /**
     * Add images children configuration
     *
     * @param NodeBuilder $children
     */
    private function addImagesChildrenConfiguration(NodeBuilder $children)
    {
        $children
            ->arrayNode('images')
            ->useAttributeAsKey('name')
            ->info('Images to attach inline')
            ->example('/path/to/file')
            ->prototype('scalar');
    }

    /**
     * Add attachments children configuration
     *
     * @param NodeBuilder $children
     */
    private function addAttachmentsChildrenConfiguration(NodeBuilder $children)
    {
        $children
            ->arrayNode('attachments')
            ->info('Files to attach')
            ->example('/path/to/file')
            ->prototype('scalar');
    }

    /**
     * Add recipients children configuration
     *
     * @param NodeBuilder $children
     */
    private function addRecipientsChildrenConfiguration(NodeBuilder $children)
    {
        $recipients = $children->arrayNode('recipients')->addDefaultsIfNotSet()->children();

        $recipients
            ->scalarNode('name')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('Recipient name')
            ->example('John Doe');

        $recipients
            ->scalarNode('email')
            ->isRequired()
            ->cannotBeEmpty()
            ->info('Recipient email')
            ->example('john.doe@gmail.com');
    }
}
