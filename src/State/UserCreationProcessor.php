<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class UserCreationProcessor implements ProcessorInterface
{
    private ProcessorInterface $decorated;
    private $twig;

    public function __construct(
        ProcessorInterface $decorated, 
        private UserPasswordHasherInterface $passwordEncoder,
        Environment $twig,
    )
    {
        $this->decorated = $decorated;
        $this->twig = $twig;
    }
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        //hashing password before persisting data
        if ($data instanceof User) {
            dump('hashing the password');
            $plainPassword = $data->getPassword();
            $hashedPassword = $this->passwordEncoder->hashPassword($data, $plainPassword);
            $data->setPassword($hashedPassword);
        }
        //send mail to the new user
        if ($data instanceof User) {

            $transport = Transport::fromDsn('smtp://impactdev3@gmail.com:xfiwqfdsggwdxmki@smtp.gmail.com:587');
            $mailer = new Mailer($transport);
            $emailContent = $this->twig->render('emails/welcome.html.twig', [
                'user' => $data,
            ]);
            $email = (new Email())
                ->from('impactdev3@gmail.com')
                ->to($data->getEmail())
                ->subject('Welcome to ImpactDev! Your Account Details Inside')
                ->html($emailContent);

            $mailer->send($email);
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
