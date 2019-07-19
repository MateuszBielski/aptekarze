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
     * @ORM\OrderBy({"paymentDate" = "DESC"})
     */
    private $contributions;

    private $historyChangesChecked = false;
    private $archiveRatesAdded = false;
    private $myHistoryCached = array();
    private $contributionsCached = array();
    private $stringCurrentAccount = 'nie obliczone';
    private $currentAccuntValue;
    private $montsReckoning;

    public function __construct()
    {
        $this->myHistory = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        $this->beginDate = new \DateTime('now');
    }

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

    public function getMyHistoryCached()
    {
        if ($this->optimized) return $this->myHistoryCached;
        else return $this->myHistory;
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
    
    public function getCurrentAccuntValue()
    {
        return $this->currentAccuntValue;
    }

    public function createTempUsername()
    {
        $this->username = $this->firstName.$this->surname.$this->telephone;
    }

    public function KindOfHistoryChanges()
    {
        //co się zmieniło względem poprzedniego wpisu
        // czy pierwszy wpis dotyczy rejestracji
        
        $numbOfRecord = count($this->getMyHistoryCached());
        if (!$numbOfRecord) return;
        
        $current = $this->getMyHistoryCached()[0];
        if ($numbOfRecord == 1) {
            //czy to jest data rejestracji
            $current->GenerateInfoChangeComparingToNext($this);
            return;
        }
        $i = 1;
        for($i;$i < $numbOfRecord ; $i++){
            $current->GenerateInfoChangeComparingToNext($this->getMyHistoryCached()[$i]);
            $current = $this->getMyHistoryCached()[$i];
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
       
        $intervalsAndRates = $this->ExtractIntervalsAndRates($day);
        $interval_months = $intervalsAndRates['intervals'];
        $valueRate = $intervalsAndRates['rates'];
        $numbOfIntervals = count($interval_months);
        // $okresy = '';
        $i = 0;
        $result = 0;
        for($i ; $i < $numbOfIntervals ; $i++) {
            $result += $interval_months[$i] * $valueRate[$i];
            // $okresy .= " + ".$interval_months[$i];
        }

        //nie dodaje tu stanu początkowego, bo będzie oddzielnie

        // return $okresy;
        return -$result;
        // $tempDate1 = $intervalStart->format('d.m.Y');
        // $tempDate2 = $intervalStop->format('d.m.Y');
        // return $tempDate1."   ".$tempDate2;
        // return $capture;
        // return $stopStartWynik;

    }
    public function ExtractIntervalsAndRates(\DateTimeInterface $day)
    {
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();
        
        $interval_months = array();
        $valueRate = array();
        $intervalStart = $day;
        $intervalStop = $day;
        //oddzielny przypadek dla sytuacji bez daty rejestracji?
        //jeżeli w pierwszym i drugim wpisie są inne stanowiska to dla okresu między nimi
        //przyjęta jest stawka z drugiego wpisu.
        $capture = '';
        if (count($this->getMyHistoryCached())) {
            // $intervalStart = clone $this->myHistoryCached[0]->getDate();
            //$intervalStart->modify('first day of next month');
            //powyższe było przy założeniu, że stawka obowiązuje od następnego miesiąca
            $intervalStart = clone $this->getMyHistoryCached()[0]->getDateRoundToMonthAccordingToDayOfChange();
        }
        if($this->beginDate != null ){
            $intervalStart = $this->getBeginDateRoundToMonthAccordingToDayOfChange();
        } 
        foreach($this->getMyHistoryCached() as $h_row) {
            $afterIntervalStart = $h_row->getDateRoundToMonthAccordingToDayOfChange() >= $intervalStart;
            if ($h_row->changeJob && $afterIntervalStart) {
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
        $valueRate[] = ($this->getMyJobRateCached() == null) ? 0 : $this->getMyJobRateCached();
        $arrayResult = array();
        $arrayResult['intervals'] = $interval_months;
        $arrayResult['rates'] = $valueRate;
        return $arrayResult;
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
        $contributions = $this->optimized ? $this->contributionsCached : $this->contributions;
        foreach($contributions as $contr)
        {
            $sum += $contr->getValue();
        }
        return $sum;
    }

    //aktualny bilans
    public function StringCurrentAccount(): string
    {
        $sign = '';
        $account = $this->PaidContributionSum()+$this->CalculateAllDueContribution()+$this->initialAccount;
        $this->currentAccuntValue = $account;
        if ($account > 0) $sign = '+';
        // if ($account < 0) $sign = '- ';
        return $sign.strval($account)." zł";
    }
    
    public function HistoryDatesToString()
    {
        $result = '';
        foreach($this->getMyHistoryCached() as $hr)
        {
            $result .= $hr->getDate()->format('d.m.Y').'   ';
        }
        return $result;
    }

    public function getStringCurrentAccount()
    {
        return $this->stringCurrentAccount;
    }



    
    public function setStringCurrentAccount($stringCurrentAccount)
    {
        $this->stringCurrentAccount = $stringCurrentAccount;
        return $this;
    }
    // public function test()
    // {
    //     // return count($this->myHistoryCached);
    //     // return $this->myJobRateCached;
    //     // return $this->CalculateAllDueContribution();
    //     return $this->StringCurrentAccount();
    //     //return 'test';
    // }

    //dodać miesiąc do ostatnio wpłaconej raty
    public function getExpectedContribution(\DateTime $date = null): Contribution
    {
        if(!count($this->contributions)) return new Contribution;
        // $lastContribution = end($this->contributions);
        $lastContribution = $this->contributions->last();
        if($date == null){
            $date = $lastContribution->getPaymentDate();
            $date->modify('+1 month');
        }
        $nextContribution = clone $lastContribution;
        $nextContribution->setPaymentDate($date);
        return $nextContribution;
    }

    public function getRegistrationPoint()
    {
        if(!count($this->myHistory)) return null;
        
        $first = $this->myHistory->first();

        if(count($this->myHistory) == 1)$second = $this;
        else $second = $this->myHistory->next();
        return $first->isRegistrationPointComaparedTo($second) ? $first : null;
             
    }
   
    public function getRegistrationDate()
    {
        $registrationPoint = $this->getRegistrationPoint();
        
        return $registrationPoint != null ? $registrationPoint->getDate() : new \DateTime('now');
    }

    //nie używać bo miesza
    public function createIfneededAndSetRegistrationDate(\DateTime $date)
    {
        $registrationPoint = $this->getRegistrationPoint();
        if ( $registrationPoint == null ) {
            $registrationPoint = new MemberHistory($this);
            $this->addMyHistory($registrationPoint);
        }
        $registrationPoint->setDate($date);
        return;
    }
    public function getBeginDateRoundToMonthAccordingToDayOfChange()
    {
        return $this->DateRoundToMonthAccordingToDayOfChange($this->beginDate);
    }
    public function getMonthsReckoning()
    {
        return $this->montsReckoning;
    }
    public function GenerateMonthsReckoning()  
    {
        $this->montsReckoning = new MonthsReckoning();
        $this->montsReckoning->takeIntervalsAndRates($this->ExtractIntervalsAndRates(new \DateTime('now')));
        $this->montsReckoning->takeAllPaidSum($this->PaidContributionSum()+$this->initialAccount);
        $this->montsReckoning->takeBeginDate($this->getBeginDate());
        $this->montsReckoning->GenerateArrayYearsMonths();
        
    }
    private function InMyFirstHitorySetChanges()
    {
        switch(count($this->myHistory)){
            case 1:
                $this->myHistory[0]->CopyData($this);
                break;
            case 0:
                $this->addMyHistory(new MemberHistory($this));
                break;
        }
       
    }
    public function ArchiveChanges(MemberHistory $beforeChanges = null)
    {
        if($beforeChanges != null)
        {
            $this->addMyHistory($beforeChanges);
            return;
        }
        $this->InMyFirstHitorySetChanges();
    }

    
    /*
     * czyli nowa historia przyjmuje dane od najbliższego wpisu z prawej p1(późniejszego)
    a ten wpis dostaje nowe dane
    jeżeli p1 jest datą rejestracji zamiana nie następuje, nowa historia dostaje nowe dane,
    a p1 pozostaje bez zmian
    jeżeli p1 po zmianie stał się taki jak jeszcze kolejny  (p2), 
    to p2 usuwamy, bo p1 stałby się drugą datą rejstracji
    
    zasada: jeżeli dwa kolejne wpisy różnią się tylko datą - pierwszy z nich
    traktowany jako data rejestracji
    
    działanie:
    nowy wpis dodać do kolekcji
    posortować datami
    wyciągnąć te, w których zmienia się job
    znaleźć p1 i p2 
    dokonać modyfikacji jak wyżej
    połączyć ze wszystkimi
    przypisać do pierwotnej kolekcji (myHistory)
    pojawi się sytuacja, że do istniejącej kolekcji dodane są więcej niż jeden wpisy(np po docelowej edycji użytkownika ajaxem i jquery), a inne usunięte
    trzeba sprawdzić, które to i odpowiednio ich sąsiadów oznaczyć
    wg analizy nazw i dostępności funkcji dla CollectionType: nie jest używana metoda setCollection lecz addToCollection( $rekord)
    analogicznie remove.
    czyli każdy wpis dodawany oddzielnie do już isniejących, z tego wynika całą powyższą procedurę trzeba przeprowadzić za każdym razem przy dodawaniu
    odwrotnie przy odejmowaniu.
    */
    public function InsertWithModifyNeighbors(MemberHistory $newHistory)
    {
        
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();

        $jobHistory = array();
        $indexNew = -1;
        $i = 0;
        //zakładamy, że myHistoryCached jest posortowana
        //zamiast sortowania newHistory trzeba od razu wstawić we właściwe miejsce
        foreach($this->getMyHistoryCached() as $h)
        {
            if($h->changeJob){
                if($indexNew < 0 && $newHistory->getDate() < $h[$i]->getDate())
                {
                    $jobHistory[] = $newHistory;
                    $indexNew = $i++;
                }
                $jobHistory[] =  $h;
                $i++;
            }
        }
        if($indexNew < 0){
            $jobHistory[] = $newHistory;
            $indexNew = $i++;
        } 
        $numbOfRecord = $i;
        $p1 = ($indexNew + 1 < $numbOfRecord) ? $jobHistory[$indexNew + 1] : null;
        $p2 = ($indexNew + 2 < $numbOfRecord) ? $jobHistory[$indexNew + 2] : null;
        if ($p1 != null)
        {
            //tu zrobić podmiankę 
        }
        if ($p2 != null)
        {
            //sprawdzić czy $p1 == $p2 wtedy 
        }
        $this->myHistory = $jobHistory;
    }
}

