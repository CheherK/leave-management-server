<?php

namespace App\Entity;

use App\Repository\LeaveRequestRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Carbon\Carbon;
use Gedmo\Mapping\Annotation as Gedmo;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\State\LeaveRequestCreationProcessor;
use App\State\LeaveRequestUpdateProcessor;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: LeaveRequestRepository::class)]
#[
    ApiResource(
        formats: ['json'],
        operations: [
            new Get(),
            new GetCollection(
                normalizationContext: [
                    'groups' => ['leaveRequest:readAll'],
                ]
            ),
            new Post(processor: LeaveRequestCreationProcessor::class),
            new Put(processor: LeaveRequestUpdateProcessor::class),
            new Patch(),
            new Delete(),
        ],
        normalizationContext: [
            'groups' => ['leaveRequest:read'],
        ]
    ),
    ApiFilter(SearchFilter::class, properties: ['status' => 'exact', 'priority' => 'exact', 'empolyee' => 'exact'])
]
class LeaveRequest
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    private ?int $id = null;

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\ManyToOne(inversedBy: 'leaveRequests')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $empolyee = null;

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Gedmo\Timestampable(on: "create")]
    private ?\DateTimeInterface $createdAt = null;

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\Column(length: 255)]
    private ?string $status = 'pending';

    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    #[ORM\Column(length: 255)]
    private ?string $reason = null;

    #[ORM\Column]
    #[Groups(['leaveRequest:read', 'leaveRequest:readAll'])]
    private ?int $priority = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpolyee(): ?User
    {
        return $this->empolyee;
    }

    public function setEmpolyee(?User $empolyee): static
    {
        $this->empolyee = $empolyee;

        return $this;
    }

    public function getCreatedAt(): string
    {
        return Carbon::instance($this->createdAt)->format('d-m-Y');
    }

    /**
     * A human-readable representation of created At entity
     */
    public function getCreatedAtAgo(): string
    {
        return Carbon::instance($this->createdAt)->diffForHumans();
    }

    public function getStartDate(): string
    {
        return Carbon::instance($this->startDate)->format('d-m-Y');
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): string
    {
        return Carbon::instance($this->endDate)->format('d-m-Y');
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(string $reason): static
    {
        $this->reason = $reason;

        return $this;
    }

    public function getPriority(): ?int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): static
    {
        $this->priority = $priority;

        return $this;
    }
}
