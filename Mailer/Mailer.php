<?php

namespace EB\EmailBundle\Mailer;

use Symfony\Component\Templating\EngineInterface;

/**
 * Class Mailer
 *
 * @author "Emmanuel BALLERY" <emmanuel.ballery@gmail.com>
 */
class Mailer
{
    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var array
     */
    private $senders;

    /**
     * @var array
     */
    private $emails;

    /**
     * @param \Swift_Mailer   $mailer     Swift Mailer
     * @param EngineInterface $templating Rendering Engine
     * @param array           $senders    Senders
     * @param array           $emails     Emails
     *
     * @throws \Exception
     */
    public function __construct(\Swift_Mailer $mailer, EngineInterface $templating, array $senders, array $emails)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->senders = $senders;
        $this->emails = $emails;
    }

    /**
     * Render an email - Can be used to display the result
     *
     * @param string $templateName Template name
     * @param array  $templateData Template data
     * @param array  $images       Images
     * @param array  $attachments  Attachments
     *
     * @return int
     * @throws \Exception
     */
    public function render($templateName, $templateData = [], $images = [], $attachments = [])
    {
        // Validate template
        if (false === isset($this->emails[$templateName])) {
            throw new \Exception('This email is not configured in eb_email.emails');
        }

        // Merge template data
        $template = $this->emails[$templateName];
        if (isset($template['globals'])) {
            $templateData = array_merge($templateData, $template['globals']);
        }
        $templateData['subject'] = $this->templating->render('EBEmailBundle::subject.html.twig', array_merge(['subjectTemplate' => $template['subject']], $templateData));

        // Attach files
        $images = array_merge($images, $template['images']);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        foreach ($images as $key => $image) {
            $templateData[$key] = 'data:' . finfo_file($finfo, $image) . ';base64,' . base64_encode(file_get_contents($image));
        }
        $attachments = array_merge($attachments, $template['attachments']);
        foreach ($attachments as $key => $attachment) {
            $templateData[$key] = sprintf('javascript:alert("will download %s")', htmlentities($attachment));
        }

        $twigTemplate = isset($template['html_template']) ? $template['html_template'] : $template['text_template'];

        return $this->templating->render($twigTemplate, $templateData);
    }

    /**
     * Send email
     *
     * @param string                          $templateName Template name
     * @param string|string[]|object|object[] $users        List of emails, or name,email
     * @param array                           $templateData Template data
     * @param array                           $images       Images
     * @param array                           $attachments  Attachments
     *
     * @return int
     * @throws \Exception
     */
    public function send($templateName, $users = [], $templateData = [], $images = [], $attachments = [])
    {
        // Validate template
        if (!isset($this->emails[$templateName])) {
            throw new \Exception('This email is not configured in eb_email.emails');
        }

        $template = $this->emails[$templateName];
        if (isset($template['globals'])) {
            $templateData = array_merge($templateData, $template['globals']);
        }

        // Users
        $cleanUsers = $this->getRecipients($templateName, $users);

        // Create the swift mailer instance
        $email = \Swift_Message::newInstance();
        $email->setContentType('text/html');
        $email->setFrom($this->senders);
        array_map([$email, 'addTo'], $cleanUsers);

        // Attach images
        $images = array_merge($images, $template['images']);
        foreach ($images as $key => $image) {
            $embeddedImage = \Swift_EmbeddedFile::fromPath($image)
                ->setFilename(basename($image))
                ->setDisposition('inline');

            $templateData[$key] = $email->embed($embeddedImage);
        }

        // Attach files
        $attachments = array_merge($attachments, $template['attachments']);
        foreach ($attachments as $attachment) {
            $attachedFile = \Swift_Attachment::fromPath($attachment)
                ->setFilename(basename($attachment))
                ->setDisposition('attachment');

            $email->attach($attachedFile);
        }

        // Rendering subject
        $subject = $this->templating->render('EBEmailBundle::subject.html.twig', array_merge(['subjectTemplate' => $template['subject']], $templateData));
        $templateData['subject'] = $subject;
        $email->setSubject($subject);

        // Rendering body (html or not)
        $textBody = $this->templating->render($template['text_template'], $templateData);
        if (isset($template['html_template'])) {
            $htmlBody = $this->templating->render($template['html_template'], $templateData);

            $email->setBody($htmlBody, 'text/html');
            $email->addPart($textBody, 'text/plain');
        } else {
            $email->setBody($textBody);
        }

        // Send
        return $this->mailer->send($email);
    }

    /**
     * Get recipients
     *
     * @param string                          $templateId Template ID
     * @param string|string[]|object|object[] $users      Users
     *
     * @return string[]
     */
    private function getRecipients($templateId, $users = [])
    {
        // Users
        $cleanUsers = [];
        $users = !is_array($users) ? [$users] : $users;

        // Add defaut recipients if configured
        if (isset($this->emails[$templateId]['recipients'])) {
            foreach ($this->emails[$templateId]['recipients'] as $recipient) {
                if (!isset($users[$recipient['name']])) {
                    $users[$recipient['name']] = $recipient['email'];
                }
            }
        }

        // Fix all users
        foreach ($users as $name => $user) {
            if (is_object($user) && method_exists($user, 'getUsername')) {
                $cleanUsers[] = $user->getUsername();
            } elseif (is_string($name) && is_string($user)) {
                $cleanUsers[$name] = $user;
            } elseif (is_string($user)) {
                $cleanUsers[] = $user;
            }
        }

        return $cleanUsers;
    }
}
