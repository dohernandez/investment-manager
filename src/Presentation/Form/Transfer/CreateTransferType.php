<?php

namespace App\Presentation\Form\Transfer;

use App\Presentation\Form\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateTransferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('beneficiaryParty', TextType::class, [
                'label' => 'Beneficiary',
                'attr' => [
                    'placeholder' => 'Choose a beneficiary',
                    'autocomplete' => "off",
                ],
            ])
            ->add('debtorParty', TextType::class, [
                'label' => 'Debtor',
                'attr' => [
                    'placeholder' => 'Choose a debtor',
                    'autocomplete' => "off",
                ],
            ])
            ->add('amount', MoneyType::class, [
                'attr' => [
                    'placeholder' => 'Enter amount',
                    'autocomplete' => "off",
                ],
                'divisor' => 100,
                'currency' => 'EUR',
            ])
            ->add('date', DateType::class, [
                'attr' => [
                    'placeholder' => 'Enter date',
                    'data-date-format' => 'DD/MM/YYYY',
                    'autocomplete' => "off",
                ],
                'input'  => 'datetime',
                'format' => 'dd/MM/yyyy',
                'label' => 'Date',
                'widget' => 'single_text',
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
            ]
        );
    }

    /**
     * This method is overwritten in this class to allow map the json request data with the form
     *
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
