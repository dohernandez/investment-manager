<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Transfer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('beneficiaryParty', EntityType::class, [
                'label' => 'Beneficiary',
                'placeholder' => 'Choose a beneficiary',
                'class' => Account::class,
            ])
            ->add('debtorParty', EntityType::class, [
                'label' => 'Debtor',
                'placeholder' => 'Choose a beneficiary',
                'class' => Account::class,
            ])
            ->add('amount', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter amount',
                ],
            ])
            ->add('date', DateType::class, [
                'attr' => [
                    'placeholder' => 'Enter date',
                    'data-date-format' => 'DD/MM/YYYY'
                ],
                'input'  => 'datetime',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date',
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Transfer::class,
        ]);
    }
}
