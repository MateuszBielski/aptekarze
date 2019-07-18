<?php

namespace App\Form;

use App\Entity\MemberHistory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Job;

class MemberHistoryJobAndDateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('date',null,['label' => false,'format'=> 'd.M.y',])
            ->add('job',EntityType::class,[
                'class' => Job::class, 
                'label' => false,
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
