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

    private $infoChangeComparingToNext;

    public $changeJob = false;

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

    public function GenerateInfoChangeComparingToNext(AbstrMember $compared)
    {
        //zwrócić jako gotowy do wyświetlenia string
        //może wcześniej zmiany znaleźć w sposób sformalizowany
        //sprawdzić czy pierwszy wpis jest datą rejestracji (czy pierwszy jest równy drugi lub aktualny)
        
        $result = '';
        if ($this->email != $compared->getEmail()) {
            $result .= "e-mail $this->email";
        };
        if ($this->telephone != $compared->getTelephone()) {
            $result .= "telefon $this->telephone";
        };
        if ($this->firstName != $compared->getFirstName()) {
            $result .= "imię $this->firstName";
        };
        if ($this->surname != $compared->getSurname()) {
            $result .= "nazwisko $this->surname";
        };
        if ($this->job != $compared->getJob()) {
            //$job = $this->job;
            $name = $this->job->getName();
            $rate = $this->job->getRate();
            $result .= "stanowisko $name $rate zł";

            $this->changeJob = true;
        };
        if ($this->paymentDayOfMonth != $compared->getPaymentDayOfMonth()) {
            $result .= "dzień płatności $this->paymentDayOfMonth";
        };
        if (!strlen($result)) {
            $result = "data rejestracji";
        } else {
            $result = "zmiana: ".$result;
        }
        $this->infoChangeComparingToNext = $result;
    }
    
    public function getInfoChangeComparingToNext()
    {
        $result = '';
        // $result .= "e-m $this->email ";
        // $result .= "tel $this->telephone ";
        // $result .= "im $this->firstName ";
        // $result .= "naz$this->surname ";
        // $name = $this->job->getName();
        // $rate = $this->job->getRate();
        // $result .= "stan $name $rate zł ";
        // $result .= "dz pł $this->paymentDayOfMonth ";
        $result .= $this->infoChangeComparingToNext;
        return $result;
    }
    
}
