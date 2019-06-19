<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Job;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ArchiveJobRepository")
 */
class ArchiveJob extends Job
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    
    /**
     * @ORM\Column(type="datetime")
     */
    private $dateOfChange;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Job", inversedBy="archiveJobs")
     * @ORM\JoinColumn(nullable=false)
     */
    private $myCurrentJob;

    public function __construct(Job $job)
    {
        $this->rate = $job->getRate();
        $this->setDateOfChange(new \DateTime('now'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    

    public function getDateOfChange(): ?\DateTimeInterface
    {
        return $this->dateOfChange;
    }

    public function setDateOfChange(\DateTimeInterface $dateOfChange): self
    {
        $this->dateOfChange = $dateOfChange;

        return $this;
    }

    public function getMyCurrentJob(): ?Job
    {
        return $this->myCurrentJob;
    }

    public function setMyCurrentJob(?Job $myCurrentJob): self
    {
        $this->myCurrentJob = $myCurrentJob;

        return $this;
    }
}
