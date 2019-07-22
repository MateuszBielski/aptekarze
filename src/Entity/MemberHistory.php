<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\MemberUser")
     */
    private $whoMadeChange;

    public function __construct(MemberUser $memberUser)
    {
        $this->CopyData($memberUser);
        $this->myUser = $memberUser;
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
        //if ($this->job != $compared->getJob()) {
        //poniższe jest przygotowaniem do historycznego różnicowania stawek
        if ($this->job->getRate() != $compared->getJob()->getRate()) {
            //$job = $this->job;
            $name = $this->job->getName();
            $rate = $this->job->getRate();
            $result .= "stanowisko $name $rate zł";

            $this->changeJob = true;
        };
        if ($this->paymentDayOfMonth != $compared->getPaymentDayOfMonth()) {
            $result .= "dzień płatności $this->paymentDayOfMonth";
        };
        if ($this->beginDate != $compared->getBeginDate())
        {
            $result .= "data początkowa ".$this->beginDate->format('d.m.Y');
        }
        if ($this->initialAccount != $compared->getInitialAccount())
        {
            if($this->initialAccount == null)$this->initialAccount = 0;
            $result .= "kwota początkowa ".$this->initialAccount;
        }
        if (!strlen($result)) {
            $result = "data rejestracji";
        } else {
            $result = "zmiana z: ".$result;
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

    public function getDateRoundToNextMonth()
    {
        $roundDate = clone $this->date;
        $roundDate->modify('first day of next month');
        return $roundDate;
    }

    //jeśli zmiana była do 15 dnia miesiąca to już należy płacić wg nowej stawki
    public function getDateRoundToMonthAccordingToDayOfChange()
    {
        return $this->DateRoundToMonthAccordingToDayOfChange($this->date);
    }

    public function AddMyArchievedRatesToCollection(ArrayCollection& $userHistory)
    {
        if (!$this->changeJob) return;
        $mu = $this->myUser;
        foreach($this->job->getArchieveJobs() as $aj)
        {
            $newHistory = new MemberHistory($mu);
            $newHistory->setDate($aj->getDateOfChange());
            $newHistory->setJob($aj);
            $userHistory[] = $newHistory;
        }
    }
    public function getWhoMadeChange(): ?MemberUser
    {
        return $this->whoMadeChange;
    }

    public function setWhoMadeChange(?MemberUser $whoMadeChange): self
    {
        $this->whoMadeChange = $whoMadeChange;

        return $this;
    }
    public function isRegistrationPointComaparedTo(AbstrMember $next)
    {   
        $this->GenerateInfoChangeComparingToNext($next);
        return $this->getInfoChangeComparingToNext() == 'data rejestracji';
    }
    public function IsRegisterDate()
    {
        return $this->infoChangeComparingToNext == "data rejestracji";
    }
    public function ReplaceDataWith(AbstrMember $other)
    {
        // if($other->IsRegisterDate())return;
        $myDate = $this->date;
        $temporary = new MemberUser();
        $temporary->CopyData($other);
        $other->CopyData($this);
        $this->CopyData($temporary);
        //dla pewności:
        $this->date = $myDate;
        //other może być memberUserem i nie ma daty
        
    }
}
