<?php

namespace App\Entity;

use App\Service\Functions;

class MonthsReckoning
{
    private $months;
    private $intervals;
    private $rates;
    private $paidSum;
    private $beginDate;
    private $yearMonthsArray;
    public $monthNames = [
        'styczeń',
        'luty',
        'marzec',
        'kwiecień',
        'maj',
        'czerwiec',
        'lipiec',
        'sierpień',
        'wrzesien',
        'październik',
        'listopad',
        'grudzień',
    ];
    public $monthRomanNumbers = [
        'I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII',
    ];
    public function GenerateArrayOfMonthWithoutYear()
    {
        $this->months = array();
        // $this->months = ['styczeń', 'luty', 'marzec', 'kwiecień'];
        //datę początkową trzeba wziąc - do zrobienia !!!!
        $i = 0;
        $j = 1;
        $numberIntervals = count($this->intervals);
        $paidSum = $this->paidSum;
        $latestRate = null;
         for($i; $i < $numberIntervals ; $i++){
             $numberMonths = $this->intervals[$i];
             $k = 0;
             for($k; $k < $numberMonths ; $k++){
                 
                $remainsContrib = $paidSum > $this->rates[$i] ? $this->rates[$i] : $paidSum;
                $paidSum -=$this->rates[$i];
                if($remainsContrib <= 0 ){
                    $remainsContrib = '<font color="#FF0000">'.'-'.$this->rates[$i].'</font>';
                }
                if($remainsContrib > 0 && $remainsContrib < $this->rates[$i]){
                    $remainsToPay = $remainsContrib - $this->rates[$i];
                    $remainsContrib = $remainsContrib.'<font color="#FF0000">/'.$remainsToPay.'</font>';
                }

                
                 $this->months[] = $remainsContrib;
                // $this->months[] = $remainsContrib.' '.$paidSum;//$j.' '.
                $j++;

                //klasy:pokrywa, nadpłata, brakuje

             }
             $latestRate = $this->rates[$i];
        }
         //nadpłata na dalsze miesiące
        while ($paidSum > 0 ) {
            if($latestRate <= 0)$latestRate = $paidSum;
            $remainsContrib = $paidSum > $latestRate ? $latestRate : $paidSum;
            $paidSum -= $latestRate;
            // $this->months[] = $remainsContrib.' '.$paidSum;
            $this->months[] = '<font color="#00FF00">'.'+'.$remainsContrib.'</font>';
        } 
    }
    public function GenerateArrayYearsMonths()
    {
        if($this->beginDate == null)return;
        $this->GenerateArrayOfMonthWithoutYear();
        $beginDate = Functions::f_DateRoundToMonthAccordingToDayOfChange($this->beginDate);
        $startYear = intval($beginDate->format('Y'));
        $startMonth = intval($beginDate->format('m')) - 1;//bo tablica indeksuje od 0
        $year = $startYear;
        $cunterMonthsWithoutYear = 0;
        $numberOfMonths = count($this->months);
        $generateYear = $numberOfMonths;
        while($generateYear){
            $emptyMonths = $this->GenerateEmptyMonths();
            $setMonths = true;
            while($setMonths){
                $emptyMonths[$startMonth] = $this->months[$cunterMonthsWithoutYear++];
                $startMonth++;
                if($startMonth >= 12 )
                {
                    $setMonths = false;
                    $startMonth = 0;
                }
                if($cunterMonthsWithoutYear >= $numberOfMonths){
                    $setMonths = false;
                    $generateYear = false;
                }
            }
            $this->yearMonthsArray[$year++] = $emptyMonths;
        }
    }
    private function GenerateEmptyMonths()
    {
        $emptyMonths = array();
        for($i = 0; $i < 12 ; $i++)
        {
            $emptyMonths[$i] = '';
        }
        return $emptyMonths;
    }
    public function getMonths(Type $var = null)
    {
        return $this->months;
    }
    public function getYearMonthsArray(Type $var = null)
    {
        return $this->yearMonthsArray;
    }
    public function takeIntervalsAndRates(array $iar)
    {
        $this->intervals = $iar['intervals'];
        $this->rates = $iar['rates'];
    }
    public function takeAllPaidSum(float $sum)
    {
        $this->paidSum = $sum;
    }

    public function takeBeginDate(\DateTime $beginDate = null)
    {
        $this->beginDate = $beginDate;
    }
   
}