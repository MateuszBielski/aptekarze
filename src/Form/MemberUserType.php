<?php

namespace App\Form;

use App\Entity\MemberUser;
use App\Entity\Job;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('telephone',null,['label' => 'telefon',])
            ->add('email',null,['label' => 'adres email',])
            ->add('firstName',null,['label' => 'imię',])
            ->add('surname',null,['label' => 'nazwisko',])
            //->add('username',null,['label' => 'nazwa użytkownika',])
            // ->add('roles')
            // ->add('password')
            ->add('job',EntityType::class,[
                'class' => Job::class, 
                'label' => 'stanowisko',
                'choice_label' => 
                // function(Pacjent $pc){return $pc->getImieInazwisko();} 
                'name'
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
