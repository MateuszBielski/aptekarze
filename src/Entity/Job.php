<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\JobRepository")
 */
class Job
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $rate;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ArchiveJob", mappedBy="myCurrentJob", orphanRemoval=true)
     */
    private $archiveJobs;

    public function __construct()
    {
        $this->archiveJobs = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    /**
     * @return Collection|ArchiveJob[]
     */
    public function getArchiveJobs(): Collection
    {
        return $this->archiveJobs;
    }

    public function addArchiveJob(ArchiveJob $archiveJob): self
    {
        if (!$this->archiveJobs->contains($archiveJob)) {
            $this->archiveJobs[] = $archiveJob;
            $archiveJob->setMyCurrentJob($this);
        }

        return $this;
    }

    public function removeArchiveJob(ArchiveJob $archiveJob): self
    {
        if ($this->archiveJobs->contains($archiveJob)) {
            $this->archiveJobs->removeElement($archiveJob);
            // set the owning side to null (unless already changed)
            if ($archiveJob->getMyCurrentJob() === $this) {
                $archiveJob->setMyCurrentJob(null);
            }
        }

        return $this;
    }
}
