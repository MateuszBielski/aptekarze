<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberHistoryRepository")
 */
class MemberHistory extends AbstrMember
{
    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberUser", inversedBy="myHistory")
     */
    private $myUser;

    public function __construct(MemberUser $memberUser)
    {
        $this->CopyData($memberUser);
        $this->setDate(new \DateTime('now'));
    }
    
    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMyUser(): ?MemberUser
    {
        return $this->myUser;
    }

    public function setMyUser(?MemberUser $myUser): self
    {
        $this->myUser = $myUser;

        return $this;
    }
    
    public function CopyData(MemberUser $memberUser)
    {
        $this->setTelephone($memberUser->getTelephone());
        $this->setEmail($memberUser->getEmail());
        $this->setFirstName($memberUser->getFirstName());
        $this->setSurname($memberUser->getSurname());
        $this->setJob($memberUser->getJob());
        $this->setPaymentDayOfMonth($memberUser->getPaymentDayOfMonth());
        $this->setMyUser($memberUser);
    }
}
