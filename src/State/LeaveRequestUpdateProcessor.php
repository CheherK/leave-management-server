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

class LeaveRequestUpdateProcessor implements ProcessorInterface
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
        $subject = '';
        switch ($mailTemplate) {
            case "requestAccepted":
                $subject = "ImpactDev News: Demande de Congé Acceptée!";
                break;
            case "requestRejected":
                $subject = "ImpactDev News: Demande de Congé Refusée!";
                break;
        }
        $email = (new Email())
            ->from("impactdev3@gmail.com")
            ->to($destination)
            ->subject($subject)
            ->html($emailContent);

        $mailer->send($email);
    }
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof LeaveRequest) {
            $priority = $data->getPriority();
            $status = $data->getStatus();
            $submitter = $data->getEmpolyee();
            $destination = $submitter->getEmail();
            $userRepository = $this->entityManager->getRepository(User::class);
            if ($priority == 1) //final decision tooked by RH
            {
                if ($status == 'accepted') {
                    $leaveBalance = $submitter->getLeaveBalance();

                    $startDateString = $data->getStartDate();
                    $endDateString = $data->getEndDate();

                    // Convert the start and end date strings to DateTime objects
                    $startDate = \DateTime::createFromFormat('d-m-Y', $startDateString);
                    $endDate = \DateTime::createFromFormat('d-m-Y', $endDateString);

                    $dateInterval = $startDate->diff($endDate);
                    $numberOfDays = $dateInterval->days + 1;

                    // Calculate the new leave balance
                    $newLeaveBalance = $leaveBalance - $numberOfDays;

                    // Update the leave balance in the User entity
                    $submitter->setLeaveBalance($newLeaveBalance);

                    // Persist changes to the database
                    $this->entityManager->persist($submitter);
                    $this->entityManager->flush();

                    $this->sendMail($data, $submitter, $destination, "requestAccepted");
                }
                if ($status == 'rejected') {
                    $this->sendMail($data, $submitter, $destination, "requestRejected");
                }
            } else if ($priority == 2) //decision tooked by a technical Director
            {
                if ($status == 'accepted') //request accepted => forward it to RH and infrom him
                {
                    $data->setPriority(1); //set priority to 1 to be forwarded to RH
                    $data->setStatus("pending"); // Set the status to "Pending" because it will be awaiting action from RH

                    //get the RH mail
                    $criteria = 'ROLE_RH';
                    $usersToInform = $userRepository->findAll();
                    foreach ($usersToInform as $user) {
                        if (in_array($criteria, $user->getRoles())) {
                            $destination = $user->getEmail();
                            $this->sendMail($data, $submitter, $destination, "newRequest");
                        }
                    }
                }
                if ($status == 'rejected') //request refused => inform user
                {
                    $this->sendMail($data, $submitter, $destination, "requestRejected");
                }
            }
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
