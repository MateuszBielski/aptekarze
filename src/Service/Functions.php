<?php

namespace App\Service;

class Functions 
{
    public static function f_DateRoundToMonthAccordingToDayOfChange(\DateTime $date)
    {
        $roundDate = clone $date;
        $day = intval($date->format('d'));
        $roundDate = ($day > 15) ? $roundDate->modify('first day of next month') : $roundDate->modify('first day of this month');
        return $roundDate;
    }

}