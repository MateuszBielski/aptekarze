<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ContributionRepository")
 */
class Contribution
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $value;

    /**
     * @ORM\Column(type="date")
     */
    private $paymentDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberUser", inversedBy="contributions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $myUser;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $printed = null;

    private $myUserCached;
    private $optimized = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPaymentDate(): ?\DateTimeInterface
    {
        return $this->paymentDate;
    }

    public function setPaymentDate(\DateTimeInterface $paymentDate): self
    {
        $this->paymentDate = $paymentDate;

        return $this;
    }

    public function getMyUser(): ?MemberUser
    {
        return $this->myUser;
    }

    public function setMyUser(?MemberUser $myUser): self
    {
        $this->myUser = $myUser;
        $this->myUserCached = $myUser;

        return $this;
    }

    public function setMyUserCached(MemberUser $mu)
    {
        $this->myUserCached = $mu;
    }

    public function setOptimized()
    {
        $this->optimized = true;
    }

    public function getMyUserCached()
    {
        if ($this->optimized) return $this->myUserCached;
        else return $this->myUser;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }
    public function getType()
    {
        $result = '';
        switch ($this->source) {
            case 1: 
            $result = 'gotówka';
            break;
            case 2: 
            $result = 'przelew';
            break;
        }
        return $result;
    }
    public function getOrCreateConfirmation()
    {
        //"wydrukowane dnia 
        $message = '';
        if ($this->printed == null)
        {
            $message = "wydrukuj";
        }
        else {
            $message = $this->printed->format('d.m.Y');
        }
        return $message;
    }

    public function getPrinted(): ?\DateTimeInterface
    {
        return $this->printed;
    }

    public function setPrinted(?\DateTimeInterface $printed): self
    {
        $this->printed = $printed;

        return $this;
    }
}
