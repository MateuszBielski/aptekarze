<?php

namespace App\Form;

use App\Entity\MemberUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MemberUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $extended = $builder->getData()->getJob()->getRate() > 0;
        if($extended){
            $builder
            ->add('firstName',null,['label' => 'imię',])
            ->add('surname',null,['label' => 'nazwisko',])
            ->add('telephone',null,['label' => 'telefon',])
            ->add('email',null,['label' => 'adres email',])
            ->add('nazwiskoPanienskie',null,['label' => 'nazwisko panieńskie',])
            ->add('nrPrawaZawodu',null,['label' => 'nr pwz',])
            ->add('paymentDayOfMonth',null,['label' => 'dzień płatności',]);
        }
        $builder
        ->add('beginDate',null,[
            'label' => 'Od tej daty liczymy składki',
            'attr' => ['class' =>'input-generate-months',],
            'format'=> 'd.M.y'
            ])
        ->add('initialAccount',null,['label' => 'suma wpłacona od daty powyżej',
            'attr' => ['class' =>'input-generate-months',],])
            //->add('username',null,['label' => 'nazwa użytkownika',])
            // ->add('roles')
            // ->add('password')
        ->add('job',EntityType::class,[
            'class' => Job::class, 
            'label' => 'obecne stanowisko',
            'choice_label' => 
            function(Job $job){return $job->getName()."  -  ".$job->getRate()." zł";},
            'choice_value' => function (Job $entity = null) {
                return $entity ? $entity->getId() : '';
            },
            'attr' => ['class' =>'input-generate-months',], 
            ])
        ->add('myJobHistory',CollectionType::class,[
                'entry_type' => MemberHistoryJobAndDateType::class,
                'label' => 'zmiany stanowisk',
                'allow_add' => true,
                'allow_delete' =>true,
                'by_reference' =>true,
                // 'prototype_data' => $options['prototype_data_opt'],
            ])
        ;
        // ->add('dawki',CollectionType::class,[
        //     'entry_type' => DawkaType::class,
        //     'allow_add' => true,
        //     'allow_delete' =>true,
        //     'by_reference' =>true,
        //     'prototype_data' => $options['prototype_data_opt'],
        //     ])
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberUser::class,
        ]);
    }
}
