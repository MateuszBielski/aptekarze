<?php

namespace App\Form;

use App\Entity\MemberUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Job;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class MemberUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $extended = true;
        $job = $builder->getData()->getJob();
        if ($job != null )
        $extended = $job->getRate() > 0;
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
            'label' => 'stanowisko',
            'choice_label' => 
            function(Job $job){return $job->getName()."  -  ".$job->getRate()." zł";},
            'choice_value' => function (Job $entity = null) {
                return $entity ? $entity->getId() : '';
            },
            'attr' => ['class' =>'input-generate-months',], 
            ])
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemberUser::class,
        ]);
    }
}
