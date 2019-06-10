<?php

namespace App\Form;

use App\Entity\Contribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\MemberUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value',null,['label' => 'kwota'])
            ->add('paymentDate')
            ->add('myUser',EntityType::class,[
                'class' => MemberUser::class,
                'choice_label' => function(MemberUser $mu){return $mu->getNameAndValue();},
                'label' => 'członek'
                ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contribution::class,
        ]);
    }
}
