<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\Operation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dateAt', DateType::class, [
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
            ->add('type', ChoiceType::class, [
                'placeholder' => 'Choose a type',
                'choices' => Operation::TYPES,
                'choice_label' => function ($choice, $key, $value) {
                    return strtoupper($value);
                },
            ])
            ->add('stock', StockChoiceType::class, [
                'placeholder' => 'Choose a stock',
                'required' => false,
            ])
            ->add('amount', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter amount',
                    'autocomplete' => "off",
                ],
                'required' => false,
            ])
            ->add('price', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter price',
                    'autocomplete' => "off",
                ],
                'required' => false,
            ])
            ->add('priceChange', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter price change',
                    'autocomplete' => "off",
                ],
                'required' => false,
            ])
            ->add('priceChangeCommission', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter price change commision',
                    'autocomplete' => "off",
                ],
                'required' => false,
            ])
            ->add('value', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter value',
                    'autocomplete' => "off",
                ]
            ])
            ->add('commission', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter commission',
                    'autocomplete' => "off",
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Operation::class,
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
