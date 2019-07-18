<?php

namespace App\Form;

use App\Entity\MemberHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Job;

class MemberHistoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('telephone')
            ->add('email')
            ->add('firstName')
            ->add('surname')
            ->add('paymentDayOfMonth')
            ->add('nrPrawaZawodu')
            ->add('nazwiskoPanienskie')
            ->add('beginDate')
            ->add('initialAccount')
            ->add('date')
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
