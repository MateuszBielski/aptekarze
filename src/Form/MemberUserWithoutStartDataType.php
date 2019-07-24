<?php

namespace App\Form;

use App\Entity\MemberUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MemberUserWithoutStartDataType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName',null,['label' => 'imię',])
            ->add('surname',null,['label' => 'nazwisko',])
            ->add('telephone',null,['label' => 'telefon',])
            ->add('email',null,['label' => 'adres email',])
            ->add('nazwiskoPanienskie',null,['label' => 'nazwisko panieńskie',])
            ->add('nrPrawaZawodu',null,['label' => 'nr pwz',])
            ->add('paymentDayOfMonth',null,['label' => 'dzień płatności',])
            ->add('job',EntityType::class,[
                'class' => Job::class, 
                'label' => 'stanowisko',
                'choice_label' => 
                function(Job $job){return $job->getName()."  -  ".$job->getRate()." zł";},
                'choice_value' => function (Job $entity = null) {
                    return $entity ? $entity->getId() : '';
                },])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberUser::class,
        ]);
    }
}