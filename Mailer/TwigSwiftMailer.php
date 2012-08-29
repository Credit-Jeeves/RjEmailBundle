<?php

namespace Rj\EmailBundle\Mailer;

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use Rj\EmailBundle\Entity\EmailTemplateManager;
use Rj\EmailBundle\Swift\Message;

/**
 * @author Jeremy Marc <jeremy.marc@me.com>
 */
class TwigSwiftMailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $parameters;
    protected $manager;

    public function __construct($mailer, RouterInterface $router, EmailTemplateManager $manager, array $parameters)
    {
        $this->mailer     = $mailer;
        $this->router     = $router;
        $this->manager    = $manager;
        $this->parameters = $parameters;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['confirmation'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->manager->renderEmail($template, null, array(
            'username' => $user->getUsername(),
            'confirmationUrl' =>  $url,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['template']['resetting'];

        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), true);
        $rendered = $this->manager->renderEmail($template, null, array(
            'username' => $user->getUsername(),
            'confirmationUrl' => $url,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['resetting'], $user->getEmail());
    }

    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        $message = Message::newInstance()
            ->setSubject($renderedTemplate['subject'])
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($renderedTemplate['body']);

        $this->mailer->send($message);
    }
}