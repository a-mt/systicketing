<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TaskRepository")
 */
class Task
{
    const ARCHIVE = 11;

    const TYPE = [
        0 => "Bug",
        1 => "Evolution",
        2 => "Info",
        3 => "Memo"
    ];

    const URGENCY = [
        0 => "Urgent",
        1 => "Major/Blocking",
        2 => "Non Blocking",
        3 => "Typo"
    ];

    const STATUS = [
        0 => "Waiting",                     // En attente
        1 => "To cost",                     // À chiffrer
        2 => "Todo",                        // À faire
        3 => "Ongoing",                     // En cours
        4 => "Q/A client",                  // Question/réponse client
        5 => "Q/A support",                 // Question/réponse support
        6 => "Prepare validation",          // À mettre en recette
        7 => "To test",                     // À recetter
        8 => "To put into production",      // À mettre en production
        9 => "In production",               // En production
        10 => "Refuse",                     // Refusé
        self::ARCHIVE => "Archive"          // Archive
    ];

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Project", inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_creation;

    /**
     * @ORM\Column(type="boolean")
     */
    private $internal = false;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\Column(type="smallint")
     */
    private $type;

    /**
     * @ORM\Column(type="smallint")
     */
    private $urgency;

    /**
     * @ORM\Column(type="smallint")
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $assigned_to;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $created_by;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskHistory", mappedBy="task", orphanRemoval=true)
     */
    private $history;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskDiscuss", mappedBy="task", orphanRemoval=true)
     */
    private $discusses;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\TaskFile", mappedBy="task", orphanRemoval=true)
     */
    private $files;

    public function __construct()
    {
        $this->history = new ArrayCollection();
        $this->discusses = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->date_creation;
    }

    public function setDateCreation(\DateTimeInterface $date_creation): self
    {
        $this->date_creation = $date_creation;

        return $this;
    }

    public function getInternal(): ?bool
    {
        return $this->internal;
    }

    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description = null): self
    {
        $this->description = $description ? $description : "";

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getUrgency(): ?int
    {
        return $this->urgency;
    }

    public function setUrgency(int $urgency): self
    {
        $this->urgency = $urgency;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAssignedTo(): ?User
    {
        return $this->assigned_to;
    }

    public function setAssignedTo(?User $assigned_to): self
    {
        $this->assigned_to = $assigned_to;

        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->created_by;
    }

    public function setCreatedBy(?User $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    /**
     * @return Collection|TaskHistory[]
     */
    public function getHistory(): Collection
    {
        return $this->history;
    }

    public function addHistory(TaskHistory $history): self
    {
        if (!$this->history->contains($history)) {
            $this->history[] = $history;
            $history->setTask($this);
        }

        return $this;
    }

    public function removeHistory(TaskHistory $history): self
    {
        if ($this->history->contains($history)) {
            $this->history->removeElement($history);
            // set the owning side to null (unless already changed)
            if ($history->getTask() === $this) {
                $history->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|discuss[]
     */
    public function getDiscusses(): Collection
    {
        return $this->discusses;
    }

    public function filterDiscusses($internal = false): Collection
    {
        return $this->discusses->filter(function(TaskDiscuss $discuss) use($internal) {
            return $internal ? $discuss->getInternal() : !$discuss->getInternal();
        });
    }

    public function addDiscuss(TaskDiscuss $discuss): self
    {
        if (!$this->discusses->contains($discuss)) {
            $this->discusses[] = $discuss;
            $discuss->setTask($this);
        }

        return $this;
    }

    public function removeDiscuss(TaskDiscuss $discuss): self
    {
        if ($this->discusses->contains($discuss)) {
            $this->discusses->removeElement($discuss);
            // set the owning side to null (unless already changed)
            if ($discuss->getTask() === $this) {
                $discuss->setTask(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TaskFile[]
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(TaskFile $file): self
    {
        if (!$this->files->contains($file)) {
            $this->files[] = $file;
            $file->setTask($this);
        }

        return $this;
    }

    public function removeFile(TaskFile $file): self
    {
        if ($this->files->contains($file)) {
            $this->files->removeElement($file);
            // set the owning side to null (unless already changed)
            if ($file->getTask() === $this) {
                $file->setTask(null);
            }
        }

        return $this;
    }
}
