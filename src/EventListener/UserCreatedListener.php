<?php

namespace App\EventListener;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\MailerInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class UserCreatedListener implements EventSubscriberInterface
{
   private $mailer;
   private $twig;

   public function __construct(MailerInterface $mailer, Environment $twig)
   {
      $this->mailer = $mailer;
      $this->twig = $twig;
   }

   public static function getSubscribedEvents()
   {
      return [
         'api.event.post_resource' => ['onUserCreated', EventPriorities::ORDER_LAST],
      ];
   }

   public function onUserCreated(ViewEvent $event)
   {
      $user = $event->getControllerResult();
      $method = $event->getRequest()->getMethod();

      if (!$user instanceof User || $method !== 'POST') {
         return;
      }

      $emailContent = $this->twig->render('emails/welcome.html.twig', [
         'user' => $user,
      ]);

      $email = (new Email())
         ->from('impactdev3@gmail.com')
         ->to($user->getEmail())
         ->subject('Welcome to ImpactDev! Your Account Details Inside')
         ->html($emailContent);

      $this->mailer->send($email);
   }

}
