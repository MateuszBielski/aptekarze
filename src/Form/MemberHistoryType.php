<?php

namespace App\Form;

use App\Entity\MemberHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Job;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MemberHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            // ->add('telephone')
            // ->add('email')
            // ->add('firstName')
            // ->add('surname')
            // ->add('paymentDayOfMonth')
            // ->add('nrPrawaZawodu')
            // ->add('nazwiskoPanienskie')
            // ->add('beginDate')
            // ->add('initialAccount')
            // ->add('date')
            ->add('firstName',null,['label' => 'imię',])
            ->add('surname',null,['label' => 'nazwisko',])
            ->add('telephone',null,['label' => 'telefon',])
            ->add('email',null,['label' => 'adres email',])
            ->add('nazwiskoPanienskie',null,['label' => 'nazwisko panieńskie',])
            ->add('nrPrawaZawodu',null,['label' => 'nr pwz',])
            ->add('paymentDayOfMonth',null,['label' => 'dzień płatności',])
            ->add('date',DateType::class,[
                'label' => 'data wpisu',
                'format'=> 'd.M.y'
                ])
            ->add('job',EntityType::class,[
                'class' => Job::class, 
                'label' => 'stanowisko',
                'choice_label' => 
                function(Job $job){return $job->getName()."  -  ".$job->getRate()." zł";} 
                ])
            // ->add('myUser')
            // ->add('whoMadeChange')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberHistory::class,
        ]);
    }
}
