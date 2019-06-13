<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberUserRepository")
 */
class MemberUser extends AbstrMember implements UserInterface
{
    
    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\MemberHistory", mappedBy="myUser")
     * @ORM\OrderBy({"date" = "ASC"})
     */
    //w widoku jest odwrócenie kolejności
    private $myHistory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="myUser")
     */
    private $contributions;

    private $historyChangesChecked = false;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $initialAccount = 0;

    public function __construct()
    {
        $this->myHistory = new ArrayCollection();
        $this->contributions = new ArrayCollection();
    }

    /*
    public function getId(): ?int
    {
        return $this->id;
    }
    */

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|MemberHistory[]
     */
    public function getMyHistory(): Collection
    {
        return $this->myHistory;
    }

    public function addMyHistory(MemberHistory $myHistory): self
    {
        if (!$this->myHistory->contains($myHistory)) {
            $this->myHistory[] = $myHistory;
            $myHistory->setMyHistory($this);
        }

        return $this;
    }

    public function removeMyHistory(MemberHistory $myHistory): self
    {
        if ($this->myHistory->contains($myHistory)) {
            $this->myHistory->removeElement($myHistory);
            // set the owning side to null (unless already changed)
            if ($myHistory->getMyHistory() === $this) {
                $myHistory->setmyHistory(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Contribution[]
     */
    public function getContributions(): Collection
    {
        return $this->contributions;
    }

    public function addContribution(Contribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setMyUser($this);
        }

        return $this;
    }

    public function removeContribution(Contribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getMyUser() === $this) {
                $contribution->setMyUser(null);
            }
        }

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

    public function createTempUsername()
    {
        $this->username = $this->firstName.$this->surname;
    }

    public function KindOfHistoryChanges()
    {
        //co się zmieniło względem poprzedniego wpisu
        // czy pierwszy wpis dotyczy rejestracji
        
        $numbOfRecord = count($this->myHistory);
        if (!$numbOfRecord) return;
        
        $current = $this->myHistory[0];
        if ($numbOfRecord == 1) {
            //czy to jest data rejestracji
            $current->GenerateInfoChangeComparingToNext($this);
            return;
        }
        $i = 1;
        for($i;$i < $numbOfRecord ; $i++){
            $current->GenerateInfoChangeComparingToNext($this->myHistory[$i]);
            $current = $this->myHistory[$i];
        }
        //ostatnia pozycja 
        $current->GenerateInfoChangeComparingToNext($this);
        $this->historyChangesChecked = true;
    }
    /* metoda obliczająca wszystkie należne składki od daty zarejestrowania + kwota(bilans - bo może być na minusie) początkowa */
    public function CalculateAllDueContribution()
    {
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();
        $interval_months = array();
        $valueRate = array();

        $IntervalToMonths = function (\DateInterval $interval) {
            $years = intval($interval->format('y'));
            $remainingMonths = intval($interval->format('m'));
            
            return $years*12 + $remainingMonths;
        };

        $intervalStart = 0;
        $intervalStop = 0;
        //oddzielny przypadek dla sytuacji bez daty rejestracji?
        $stopStartWynik = 'startStop ';
        if (count($this->myHistory)) $intervalStart = clone $this->myHistory[0]->getDate();
        foreach($this->myHistory as $h_row) {
            if ($h_row->changeJob) {
                $intervalStop = clone $h_row->getDateRoundToNextMonth();
                $stopStartWynik .= "+ ".$intervalStart->format('d.m.y')." -> ".$intervalStop->format('d.m.y');
                //$interval = $datetime1->diff($datetime2);
                $interval_months[] = $IntervalToMonths($intervalStop->diff($intervalStart));
                $valueRate[] = $h_row->getJob()->getRate();
                $intervalStart = clone $intervalStop;
            }
        }
        $result = 0;
        //ostatni okres to porównanie do zakończonego miesiąca + sprawdzenie, czy w tym miesiącu jesteśmy po dniu płatności
        $today = new \DateTime('now');
        $begThisMonth =  clone $today;
        $begThisMonth->modify('first day of this month');
        $begNextMonth = clone $today;
        $begNextMonth->modify('first day of next month');
        $intervalStop = $this->AfterPaymentDay() ? $begNextMonth : $begThisMonth;

        $stopStartWynik .= "+ ".$intervalStart->format('d.m.y')." -> ".$intervalStop->format('d.m.y');
        $interval_months[] = $IntervalToMonths($intervalStop->diff($intervalStart));
        $valueRate[] = $this->job->getRate();

        $numbOfIntervals = count($interval_months);
        // if (!$numbOfIntervals) {
            
        // }
        $okresy = '';
        $i = 0;
        for($i ; $i < $numbOfIntervals ; $i++) {
            $result += $interval_months[$i] * $valueRate[$i];
            $okresy .= " + ".$interval_months[$i];
        }
        $result += $this->initialAccount;

        //return $result;
        return $stopStartWynik;
    }

    //czy jesteśmy po dniu płatności
    public function AfterPaymentDay()
    {
        $today = new \DateTime('now');
        $dayOfToday = intval($today->format('d'));
        return $dayOfToday > $this->paymentDayOfMonth;
    }

    
}
