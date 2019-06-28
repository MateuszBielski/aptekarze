<?php

namespace App\Form;

use App\Entity\Contribution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\MemberUser;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ContributionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('value',null,['label' => 'kwota'])
            ->add('paymentDate',null,['label' => 'data wpłaty'])
            ->add('source',ChoiceType::class,[
                'choices'  => [
                    'gotówka'=> 1,
                    'przelew' => 2,
                ],'label' => 'rodzaj wpłaty'
            ])
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
