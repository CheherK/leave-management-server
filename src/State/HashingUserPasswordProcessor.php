<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;

class HashingUserPasswordProcessor implements ProcessorInterface
{
    private ProcessorInterface $decorated;

    public function __construct(ProcessorInterface $decorated, private UserPasswordHasherInterface $passwordEncoder)
    {
        $this->decorated = $decorated;
    }
    public function process($data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dump('ALIVE!');
        
        if ($data instanceof User) {
            dump('hashing the password');
            $plainPassword = $data->getPassword();
            $hashedPassword = $this->passwordEncoder->hashPassword($data, $plainPassword);
            $data->setPassword($hashedPassword);
        }
        return $this->decorated->process($data, $operation, $uriVariables, $context);
    }
}
