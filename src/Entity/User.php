<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\ApiResource;
use App\State\UserCreationProcessor;
use App\State\UserUpdateProcessor;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Carbon\Carbon;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ApiResource(
    formats: ['json'],
    operations: [
        new Get(),
        new GetCollection(
            normalizationContext: ['groups' => ['user:readAll'],]
        ),
        new Post(processor: UserCreationProcessor::class),
        new Put(processor: UserUpdateProcessor::class),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => ['user:read'],
    ]
)]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:readAll', 'leaveRequest:readAll'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:readAll', 'leaveRequest:readAll'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:readAll', 'leaveRequest:readAll'])]
    private ?string $lastName = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:readAll'])]
    private ?int $phone = null;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:readAll'])]
    private ?bool $isActive = null;

    #[ORM\Column(type: 'json')]
    #[Groups(['user:read'])]
    private array $roles = [];

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $jobTitle = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?\DateTimeInterface $birthday = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?int $childNumber = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $familySituation = null;

    #[ORM\OneToMany(mappedBy: 'empolyee', targetEntity: LeaveRequest::class)]
    #[Groups(['user:read'])]
    private Collection $leaveRequests;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column]
    #[Groups(['user:read', 'user:readAll'])]
    private ?int $leaveBalance = null;

    #[ORM\Column(length: 255)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $rib = null;
    
    #[ORM\Column(length: 255)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $bank = null;

    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?string $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?\DateTimeInterface $contractStartDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?\DateTimeInterface $contractEndDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['user:read', 'user:readAll'])]
    private ?float $salary = null;

    #[ORM\Column(type: 'boolean')]
    #[Groups(['user:read'])]
    private ?bool $isFirstLogin = true;

    public function __construct()
    {
        $this->leaveRequests = new ArrayCollection();
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhone(): ?int
    {
        return $this->phone;
    }

    public function setPhone(int $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function isIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getJobTitle(): ?string
    {
        return $this->jobTitle;
    }

    public function setJobTitle(string $jobTitle): static
    {
        $this->jobTitle = $jobTitle;

        return $this;
    }

    public function getBirthday(): string
    {
        return Carbon::instance($this->birthday)->format('d-m-Y');
    }

    public function setBirthday(\DateTimeInterface $birthday): static
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getChildNumber(): ?int
    {
        return $this->childNumber;
    }

    public function setChildNumber(?int $childNumber): static
    {
        $this->childNumber = $childNumber;

        return $this;
    }

    public function getFamilySituation(): ?string
    {
        return $this->familySituation;
    }

    public function setFamilySituation(string $familySituation): static
    {
        $this->familySituation = $familySituation;

        return $this;
    }

    /**
     * @return Collection<int, LeaveRequest>
     */
    public function getLeaveRequests(): Collection
    {
        return $this->leaveRequests;
    }

    public function addLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if (!$this->leaveRequests->contains($leaveRequest)) {
            $this->leaveRequests->add($leaveRequest);
            $leaveRequest->setEmpolyee($this);
        }

        return $this;
    }

    public function removeLeaveRequest(LeaveRequest $leaveRequest): static
    {
        if ($this->leaveRequests->removeElement($leaveRequest)) {
            if ($leaveRequest->getEmpolyee() === $this) {
                $leaveRequest->setEmpolyee(null);
            }
        }

        return $this;
    }

    public function getLeaveBalance(): ?int
    {
        return $this->leaveBalance;
    }

    public function setLeaveBalance(int $leaveBalance): static
    {
        $this->leaveBalance = $leaveBalance;

        return $this;
    }
    public function getRib(): ?string
    {
        return $this->rib;
    }

    public function setRib(string $rib): static
    {
        $this->rib = $rib;

        return $this;
    }

    public function getBank(): ?string
    {
        return $this->bank;
    }

    public function setBank(string $bank): static
    {
        $this->bank = $bank;

        return $this;
    }
    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getContractStartDate(): string
    {
        return Carbon::instance($this->contractStartDate)->format('d-m-Y');
    }

    public function setContractStartDate(\DateTimeInterface $contractStartDate): static
    {
        $this->contractStartDate = $contractStartDate;

        return $this;
    }

    public function getContractEndDate(): string
    {
        return Carbon::instance($this->contractStartDate)->format('d-m-Y');
    }

    public function setContractEndDate(\DateTimeInterface $contractEndDate): static
    {
        $this->contractEndDate = $contractEndDate;

        return $this;
    }

    public function getSalary(): ?float
    {
        return $this->salary;
    }

    public function setSalary(?float $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function isIsFirstLogin(): ?bool
    {
        return $this->isFirstLogin;
    }

    public function setIsFirstLogin(bool $isFirstLogin): static
    {
        $this->isFirstLogin = $isFirstLogin;

        return $this;
    }
}
