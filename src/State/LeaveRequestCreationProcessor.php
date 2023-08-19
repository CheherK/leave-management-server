<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\LeaveRequest;
use App\Entity\User;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Doctrine\ORM\EntityManagerInterface;

class LeaveRequestCreationProcessor implements ProcessorInterface
{
    private ProcessorInterface $decorated;
    private $twig;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ProcessorInterface $decorated,
        Environment $twig,
        EntityManagerInterface $entityManager
    ) {
        $this->decorated = $decorated;
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }
    private function sendMail($data, $user, $destination, string $mailTemplate)
    {
        $transport = Transport::fromDsn('smtp://impactdev3@gmail.com:xfiwqfdsggwdxmki@smtp.gmail.com:587');
        $mailer = new Mailer($transport);
        $emailContent = $this->twig->render("emails/$mailTemplate.html.twig", [
            'user' => $user,
            'leaveRequest' => $data
        ]);
        $email = (new Email())
            ->from("impactdev3@gmail.com")
            ->to($destination)
            ->subject("Demande de Congé Soumise: Action Requise")
            ->html($emailContent);

        $mailer->send($email);
    }
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof LeaveRequest) {
            $priority = $data->getPriority();
            if ($priority == 0) //request from RH will be accepted automatically
            {
                $data->setStatus('accepted');
            } else  //request from technoical Director or Other roles
            {
                $submitter = $data->getEmpolyee();
                if ($priority == 2) {
                    $criteria = "ROLE_DR";
                } else if ($priority == 1) {
                    $criteria = "ROLE_RH";
                }
                $usersToInform = $this->entityManager->getRepository(User::class)->findAll();
                foreach ($usersToInform as $user) {
                    if (in_array($criteria, $user->getRoles())) {
                        $destination = $user->getEmail();
                        $this->sendMail($data, $submitter, $destination, "newRequest");
                    }
                }
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
