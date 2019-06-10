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
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberUser", inversedBy="myUser")
     */
    private $myUser;

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
}
