<?php

namespace App\Form;

use App\Entity\Transfer;
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
            ->add('beneficiaryParty', AccountChoiceType::class, [
                'label' => 'Beneficiary',
                'placeholder' => 'Choose a beneficiary',
            ])
            ->add('debtorParty', AccountChoiceType::class, [
                'label' => 'Debtor',
                'placeholder' => 'Choose a debtor',
            ])
            ->add('amount', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter amount',
                    'autocomplete' => "off",
                ],
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
        $resolver->setDefaults([
            'data_class' => Transfer::class,
        ]);
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
