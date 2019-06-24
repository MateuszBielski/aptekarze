<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MemberUserRepository")
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
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
     * @ORM\OneToMany(targetEntity="App\Entity\MemberHistory", mappedBy="myUser", cascade = {"persist","remove"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    //w widoku jest odwrócenie kolejności
    private $myHistory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="myUser", cascade = {"persist","remove"})
     */
    private $contributions;

    private $historyChangesChecked = false;
    private $archiveRatesAdded = false;
    private $myHistoryCached = array();
    private $contributionsCached = array();
    

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
            $myHistory->setMyUser($this);
        }
        $this->myHistoryCached[] = $myHistory;
        return $this;
    }

    public function addMyHistoryDirectly(MemberHistory $history)
    {
        $this->myHistoryCached[] = $history;
    }

    public function removeMyHistory(MemberHistory $myHistory): self
    {
        if ($this->myHistory->contains($myHistory)) {
            $this->myHistory->removeElement($myHistory);
            // set the owning side to null (unless already changed)
            if ($myHistory->getMyUser() === $this) {
                $myHistory->setmyUser(null);
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
        $this->contributionsCached[] = $contribution;
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
    public function getContributionsCached()
    {
        return $this->contributionsCached;
    }

    public function addContributionCached(Contribution $contr)
    {
        $this->contributionsCached[] = $contr;
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
        $this->username = $this->firstName.$this->surname.$this->telephone;
    }

    public function KindOfHistoryChanges()
    {
        //co się zmieniło względem poprzedniego wpisu
        // czy pierwszy wpis dotyczy rejestracji
        
        $numbOfRecord = count($this->myHistoryCached);
        if (!$numbOfRecord) return;
        
        $current = $this->myHistoryCached[0];
        if ($numbOfRecord == 1) {
            //czy to jest data rejestracji
            $current->GenerateInfoChangeComparingToNext($this);
            return;
        }
        $i = 1;
        for($i;$i < $numbOfRecord ; $i++){
            $current->GenerateInfoChangeComparingToNext($this->myHistoryCached[$i]);
            $current = $this->myHistoryCached[$i];
        }
        //ostatnia pozycja 
        $current->GenerateInfoChangeComparingToNext($this);
        $this->historyChangesChecked = true;
    }

    public function MyExtendedHistoryWithArchiveRates_Sorted()
    {
        $exendedHistory = $this->myHistoryCached;
        if ($this->archiveRatesAdded) return;
        foreach ($this->myHistoryCached as $r_history) {
            $r_history->AddMyArchievedRatesToCollection($exendedHistory);
        }
        $this->archiveRatesAdded = true;

        $iterator = $exendedHistory->getIterator();
        $iterator->uasort(function($aj, $bj) {
            $date_a = $aj->getDate();
            $date_b = $bj->getDate();
            return ($date_a < $date_b) ? -1 : 1;
        });
        return new ArrayCollection(iterator_to_array($iterator));
    }

    /* metoda obliczająca wszystkie należne składki od daty zarejestrowania + kwota(bilans - bo może być na minusie) początkowa */
    public function CalculateAllDueContributionOn(\DateTimeInterface $day)
    {
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();
        $interval_months = array();
        $valueRate = array();

        $intervalStart = $day;//gdyby historia była pusta
        $intervalStop = $day;
        //oddzielny przypadek dla sytuacji bez daty rejestracji?
        //jeżeli w pierwszym i drugim wpisie są inne stanowiska to dla okresu między nimi
        //przyjęta jest stawka z drugiego wpisu.
        $capture = '';
        if (count($this->myHistoryCached)) {
            // $intervalStart = clone $this->myHistoryCached[0]->getDate();
            //$intervalStart->modify('first day of next month');
            //powyższe było przy założeniu, że stawka obowiązuje od następnego miesiąca
            $intervalStart = clone $this->myHistoryCached[0]->getDateRoundToMonthAccordingToDayOfChange();
        }
        foreach($this->myHistoryCached as $h_row) {
            if ($h_row->changeJob) {
                //$intervalStop = clone $h_row->getDateRoundToNextMonth();
                //powyższe było przy założeniu, że stawka obowiązuje od następnego miesiąca
                $intervalStop = clone $h_row->getDateRoundToMonthAccordingToDayOfChange();
                // $stopStartWynik .= "+ ".$intervalStart->format('d.m.y')." -> ".$intervalStop->format('d.m.y');
                $interval_months[] = $this->DatesDiffToMonth($intervalStart, $intervalStop);
                $valueRate[] = $h_row->getMyJobRateCached();
                $intervalStart = clone $intervalStop;
            }
        }
        // $capture = $intervalStart->format('d.m.Y');
        $result = 0;
        //ostatni okres to porównanie do zakończonego miesiąca + sprawdzenie, czy w tym miesiącu jesteśmy po dniu płatności
        
        $begThisMonth =  clone $day;
        $begThisMonth->modify('first day of this month');
        $begNextMonth = clone $day;
        $begNextMonth->modify('first day of next month');
        $intervalStop = $this->AfterPaymentDay($day) ? $begNextMonth : $begThisMonth;

        //sytuacja: zarejestrowany po 15: w dniu rejestracji jest taka sytuacja:
        //intervalStop < intervalStart, więc:
        if ($intervalStop < $intervalStart) $intervalStop = clone $intervalStart;

        // $stopStartWynik .= "+ ".$intervalStart->format('d.m.y')." -> ".$intervalStop->format('d.m.y');
        $interval_months[] = $this->DatesDiffToMonth($intervalStart, $intervalStop);
        $valueRate[] = ($this->myJobRateCached == null) ? 0 : $this->getMyJobRateCached();

        $numbOfIntervals = count($interval_months);
        // $okresy = '';
        $i = 0;
        for($i ; $i < $numbOfIntervals ; $i++) {
            $result += $interval_months[$i] * $valueRate[$i];
            // $okresy .= " + ".$interval_months[$i];
        }
        $result += $this->initialAccount;

        // return $okresy;
        return $result;
        // $tempDate1 = $intervalStart->format('d.m.Y');
        // $tempDate2 = $intervalStop->format('d.m.Y');
        // return $tempDate1."   ".$tempDate2;
        // return $capture;
        // return $stopStartWynik;

    }

    public static function IntervalToMonths(\DateInterval $interval) {
        $years = intval($interval->format('%Y'));
        $remainingMonths = intval($interval->format('%M'));
        $remainingDays = intval($interval->format('%D'));
        
        //return $remainingDays;
        return $years*12 + $remainingMonths;
    }

    public static function DatesDiffToMonth($start, $stop)
    {
        //opis wymagań: ile jest pełnych miesięcy między dwoma datami, każda to pierwszy dzień miesiąca
        //metoda IntervalToMonths nie zawsze się sprawdza
        //więc trzeba inaczej
        //najpierw pełne lata:

        if (!$start instanceof \DateTimeInterface) return 0;
        if (!$stop instanceof \DateTimeInterface) return 0;
        //1999-11-1  2001-2-1
        $years = intval($stop->format('Y')) - intval($start->format('Y'));//Y - czterocyfrowo rok
        //nawet liczby ujemne powinny być ok
        $months = intval($stop->format('m')) - intval($start->format('m'));
        return $years*12 + $months;
    }

    public function CalculateAllDueContribution()
    {
        $today = new \DateTime('now');
        return $this->CalculateAllDueContributionOn($today);
    }

    //czy jesteśmy po dniu płatności
    public function AfterPaymentDay(\DateTimeInterface $day)
    {
        // day = new \DateTime('now');
        $dayOfToday = intval($day->format('d'));
        return $dayOfToday > $this->paymentDayOfMonth;
    }

    public function CreateDummyData()
    {
        $this->firstName = 'imię';
        $this->surname = 'nazwisko';
        $this->telephone = '1234';
        $this->email = 'a@b';
    }

    public function PaidContributionSum()
    {
        $sum  = 0;
        foreach($this->contributionsCached as $contr)
        {
            $sum += $contr->getValue();
        }
        return $sum;
    }

    //aktualny bilans
    public function StringCurrentAccount(): string
    {
        $sign = '';
        $account = $this->PaidContributionSum()-$this->CalculateAllDueContribution();
        if ($account > 0) $sign = '+ ';
        // if ($account < 0) $sign = '- ';
        return $sign.strval($account)." zł";
    }

    public function test()
    {
        // return count($this->myHistoryCached);
        // return $this->myJobRateCached;
        // return $this->CalculateAllDueContribution();
        return $this->StringCurrentAccount();
        //return 'test';
    }

    
}

