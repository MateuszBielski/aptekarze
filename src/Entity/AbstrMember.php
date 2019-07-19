<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Service\Functions;


/**
 * @ORM\Entity(repositoryClass="App\Repository\AbstrMemberRepository")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discriminator")
 */
abstract class AbstrMember
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $telephone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $firstName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $surname;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Job", cascade = {"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    protected $job;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    protected $paymentDayOfMonth = 20;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $nrPrawaZawodu;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $nazwiskoPanienskie;

    protected $myJobRateCached;
    protected $optimized = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    protected $beginDate = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    protected $initialAccount = 0;

    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getJob(): ?Job
    {
        return $this->job;
    }

    public function setJob(?Job $job): self
    {
        $this->job = $job;
        if(null != $job)$this->myJobRateCached = $job->getRate();
        return $this;
    }

    public function getPaymentDayOfMonth(): ?int
    {
        return $this->paymentDayOfMonth;
    }

    public function setPaymentDayOfMonth(?int $paymentDayOfMonth): self
    {
        $this->paymentDayOfMonth = $paymentDayOfMonth;

        return $this;
    }

    public function getNameAndValue()
    {
        $name = "$this->firstName $this->surname ";
        $value = $this->job->getRate()." zł";
        return $name.$value;
    }
    
    public function getMyJobRateCached()
    {
        if ($this->optimized) return $this->myJobRateCached;
        else if($this->job != null) return $this->job->getRate();
    }
    public function setMyJobRateCached($rate)
    {
        $this->myJobRateCached = $rate;
    }
     
    public function setOptimizedTrue()
    {
        $this->optimized = true;
    }

    public function getNrPrawaZawodu(): ?string
    {
        return $this->nrPrawaZawodu;
    }

    public function setNrPrawaZawodu(?string $nrPrawaZawodu): self
    {
        $this->nrPrawaZawodu = $nrPrawaZawodu;

        return $this;
    }

    public function getNazwiskoPanienskie(): ?string
    {
        return $this->nazwiskoPanienskie;
    }

    public function setNazwiskoPanienskie(?string $nazwiskoPanienskie): self
    {
        $this->nazwiskoPanienskie = $nazwiskoPanienskie;

        return $this;
    }

    public function getBeginDate(): ?\DateTimeInterface
    {
        if($this->beginDate == null)
        return new \DateTime('now');
        return $this->beginDate;
    }

    public function setBeginDate(?\DateTimeInterface $beginDate): self
    {
        $this->beginDate = $beginDate;

        return $this;
    }

    public function getInitialAccount(): ?float
    {
        return $this->initialAccount;
    }

    public function setInitialAccount(?float $initialAccount): self
    {
        $this->initialAccount = $initialAccount;

        return $this;
    } 
    
    protected function DateRoundToMonthAccordingToDayOfChange(\DateTime $date)
    {
        return Functions::f_DateRoundToMonthAccordingToDayOfChange($date);
        // DateRound($date);
    }
    public function CopyData(AbstrMember $from)
    {
        $this->setTelephone($from->getTelephone());
        $this->setEmail($from->getEmail());
        $this->setFirstName($from->getFirstName());
        $this->setSurname($from->getSurname());
        $this->setJob($from->getJob());
        $this->setPaymentDayOfMonth($from->getPaymentDayOfMonth());
        $this->setBeginDate($from->getBeginDate());
        $this->setInitialAccount($from->getInitialAccount());
        $this->setNazwiskoPanienskie($from->getNazwiskoPanienskie());
        $this->setNrPrawaZawodu($from->getNrPrawaZawodu());
    }
}
