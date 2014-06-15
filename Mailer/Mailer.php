<?php

namespace EB\EmailBundle\Mailer;

use EB\DoctrineBundle\Entity\UserInterface;
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
     * @param \Swift_Mailer     $mailer     Mailer
     * @param \Twig_Environment $templating Templating
     * @param array             $senders    Senders
     * @param array             $emails     Emails
     *
     * @throws \Exception
     */
    public function __construct(\Swift_Mailer $mailer, \Twig_Environment $templating, array $senders, array $emails)
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
        $templateData['subject'] = $this->templating->render($template['subject'], $templateData);

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

        return $this->templating->render($template['template'], $templateData);
    }

    /**
     * Send email
     *
     * @param string                               $templateName Template name
     * @param string|UserInterface|UserInterface[] $users        Users : emails, name=>email, UserInterface
     * @param array                                $templateData Template data
     * @param array                                $images       Images
     * @param array                                $attachments  Attachments
     *
     * @return int
     * @throws \Exception
     */
    public function send($templateName, $users = [], $templateData = [], $images = [], $attachments = [])
    {
        // Validate template
        if (false === isset($this->emails[$templateName])) {
            throw new \Exception('This email is not configured in eb_email.emails');
        }
        $template = $this->emails[$templateName];
        if (isset($template['globals'])) {
            $templateData = array_merge($templateData, $template['globals']);
        }

        // Users
        $cleanUsers = [];
        $users = false === is_array($users) ? [$users] : $users;
        foreach ($users as $name => $user) {
            if (is_object($user) && $user instanceof UserInterface) {
                $cleanUsers[] = $user->getUsername();
            } elseif (is_string($name) && is_string($user)) {
                $cleanUsers[$name] = $user;
            } elseif (is_string($user)) {
                $cleanUsers[] = $user;
            } else {
                throw new \Exception('Unable to add this email');
            }
        }

        // Create the swift mailer instance
        $email = \Swift_Message::newInstance();
        $email->setContentType('text/html');
        $email->setFrom($this->senders);
        $email->setSubject($subject = $this->templating->render($template['subject'], $templateData));
        array_map([$email, 'addTo'], $cleanUsers);
        $templateData['subject'] = $subject;

        // Attach files
        $images = array_merge($images, $template['images']);
        foreach ($images as $key => $image) {
            $templateData[$key] = $email->embed($image = \Swift_EmbeddedFile::fromPath($image)->setFilename(basename($image))->setDisposition('inline'));
        }
        $attachments = array_merge($attachments, $template['attachments']);
        foreach ($attachments as $attachment) {
            $email->attach(\Swift_Attachment::fromPath($attachment)->setFilename(basename($attachment))->setDisposition('attachment'));
        }
        $email->setBody($this->templating->render($template['template'], $templateData));

        // Send
        return $this->mailer->send($email);
    }
}
