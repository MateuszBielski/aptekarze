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
     * @ORM\OneToMany(targetEntity="App\Entity\MemberHistory", 
     * mappedBy="myUser", 
     * cascade = {"persist","remove"},
     * )
     * @ORM\OrderBy({"date" = "ASC"})
     */
    //orphanRemoval=true)
    //w widoku jest odwrócenie kolejności
    private $myHistory;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Contribution", mappedBy="myUser", cascade = {"persist","remove"})
     * @ORM\OrderBy({"paymentDate" = "DESC"})
     */
    private $contributions;

    private $historyChangesChecked = false;
    private $archiveRatesAdded = false;
    private $myHistoryCachedSorted = false;
    private $myHistoryCached = array();
    private $contributionsCached = array();
    private $stringCurrentAccount = 'nie obliczone';
    private $currentAccuntValue;
    private $montsReckoning;

    public function __construct()
    {
        $this->myHistory = new ArrayCollection();
        $this->contributions = new ArrayCollection();
        //poniższe wyłączone bo 5 testów nie wychodziło 19 lip 2019
        // $this->beginDate = new \DateTime('now');
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

    public function SortMyHistoryCached()
    {
        if ($this->myHistoryCachedSorted) return;
        $history = $this->getMyHistoryCached();
        if(!is_array($history)){
            $history = $history->toArray();
        }

        uasort($history,function($a, $b) {
            if ($a->getDate() == $b->getDate()) {
                return 0;
            }
            return ($a->getDate() < $b->getDate()) ? -1 : 1;
        });

        $historyCollection = new ArrayCollection();
        foreach($history as $h)
        {
            $historyCollection[] = $h;
        }
        if($this->optimized){
            $this->myHistoryCached = $historyCollection;
        }else{
            $this->myHistory = $historyCollection;
        }
        
        $this->myHistoryCachedSorted = true;
    }
    public function getMyHistoryCachedSorted()
    {
        if (!$this->myHistoryCachedSorted) $this->SortMyHistoryCached();
        
        return $this->optimized ? $this->myHistoryCached : $this->myHistory;
    }
    public function getMyJobHistory(): array
    {
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();
        $jobHistory = array();
        foreach ($this->getMyHistoryCached() as $h_row) {
            if($h_row->changeJob)
            $jobHistory[] = $h_row;
        }
        return $jobHistory;
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
        $key = array_search ($myHistory, $this->myHistoryCached);
        unset($this->myHistoryCached[$key]);
        
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
        $this->SortMyHistoryCached();
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
        $this->montsReckoning->takeBeginDate($this->beginDate);
        $this->montsReckoning->GenerateArrayYearsMonths();
        
    }
    private function InMyFirstHitorySetChanges()
    {
        switch(count($this->myHistory)){
            case 1:
                $this->myHistory[0]->CopyData($this);
                $this->myHistory[0]->setMyUser($this);
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
    public function addMyJobHistory(MemberHistory $newHistory)
    {
        
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();

        $jobHistory = new ArrayCollection();
        $NoJobHistory = array();
        $indexNew = -1;
        $i = 0;
        //zakładamy, że myHistoryCached jest posortowana
        //zamiast sortowania newHistory trzeba od razu wstawić we właściwe miejsce
        // $result = 'pierwotnie '.count($this->getMyHistoryCached());
        foreach($this->getMyHistoryCached() as $h)
        {
            if($h->changeJob or $h->IsRegisterDate()){
                if($indexNew < 0 && $newHistory->getDate() < $h->getDate())
                {
                    $jobHistory[] = $newHistory;
                    $indexNew = $i++;
                }
                $jobHistory[] =  $h;
                $i++;
                continue;
            }
            $NoJobHistory[] = $h;
        }
        if($indexNew < 0){
            $jobHistory[] = $newHistory;
            $indexNew = $i++;
        } 
        $numbOfRecord = $i;
        $result = "indexNew: ".$indexNew." numbOfRecord: ".$numbOfRecord;
        $distanceToEnd = $numbOfRecord - $indexNew;
        if($distanceToEnd < 0) $distanceToEnd = 0;
        switch($distanceToEnd){
            case 0:
            break;
            case 1:
                $p1 = $this;
                $newHistory->ReplaceDataWith($p1);
            break;
            case 2:
                $p1 = $jobHistory[$indexNew + 1];
                $p2 = $this;
                if($p1->IsRegisterDate())$p2->CopyData($newHistory);
                $newHistory->ReplaceDataWith($p1);
            break;
            default:
                $p1 = $jobHistory[$indexNew + 1];
                $p2 = $jobHistory[$indexNew + 2];
                if($p1->IsRegisterDate())$p2->CopyData($newHistory);
                $newHistory->ReplaceDataWith($p1);
                
                // $notDateRegBefore = !$p1->IsRegisterDate();
                // $p1->GenerateInfoChangeComparingToNext($p2);
                // $dateRegAfter = $p1->IsRegisterDate();
                // if($notDateRegBefore && $dateRegAfter)$jobHistory->removeElement($p1);


        }
        
        foreach ($NoJobHistory as $njh) {
            $jobHistory[] = $njh;
        }

        $this->myHistory = $jobHistory;
        
        return $result;
    }

    public function removeMyJobHistory(MemberHistory $history)
    {
        if (!$this->historyChangesChecked) $this->KindOfHistoryChanges();

        $jobHistory = new ArrayCollection();
        $indexToDelete = -1;
        $i = 0;
        
        //w testach, bez użycia bazy danych historia musi być jawnie posortowana
        foreach($this->getMyHistoryCached() as $h)
        {
            if($h->changeJob || $h->IsRegisterDate()){
                $jobHistory[] =  $h;
                //w testach history nie mają unikalnego id
                if($history->getId() == $h->getId() && $history->getDate() == $h->getDate()){
                    $indexToDelete = $i; 
                }
                $i++;
            }
        }
        if($indexToDelete < 0)return;
        $numbOfRecord = $i;
        $p1 = ($indexToDelete + 1 < $numbOfRecord) ? $jobHistory[$indexToDelete + 1] : $this;
        if($p1->IsRegisterDate()){
            $p2 = ($indexToDelete + 2 < $numbOfRecord) ? $jobHistory[$indexToDelete + 2] : $this;
            $p2->CopyData($history);
        }
        $history->ReplaceDataWith($p1);
        // $content .='po replace: '.$p1->getJob()->getRate();
        $this->myHistory = $jobHistory;
        $content = 'numbOfRecord '.$numbOfRecord.', indexToDelete '.$indexToDelete;
        foreach($this->myHistory as $h){
            $content .= ' '.$h->getDate()->format('d.m.Y').' '.$h->getJob()->getRate();
            
        }
        $this->myHistory->removeElement($history);
        
        return $content;
    }
    public function IsRegisterDate()
    {
        return false;
        //na potrzeby MemberHistory::ReplaceDataWith
    }

    public function InsertJobAsChange(Job $newJob)
    {
        $previousUserData = new MemberHistory($this);
        $this->job = $newJob;
        $this->ArchiveChanges($previousUserData);

    }
}

