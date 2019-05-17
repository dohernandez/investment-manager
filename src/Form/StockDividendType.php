<?php

namespace App\Form;

use App\Entity\StockDividend;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockDividendType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('exDate', DateType::class, [
                'attr' => [
                    'placeholder' => 'Enter ex date',
                    'data-date-format' => 'DD/MM/YYYY',
                    'autocomplete' => "off",
                ],
                'input'  => 'datetime',
                'format' => 'dd/MM/yyyy',
                'label' => 'Ex. Date',
                'widget' => 'single_text',
            ])
            ->add('paymentDate', DateType::class, [
                'attr' => [
                    'placeholder' => 'Enter payment date',
                    'data-date-format' => 'DD/MM/YYYY',
                    'autocomplete' => "off",
                ],
                'input'  => 'datetime',
                'format' => 'dd/MM/yyyy',
                'label' => 'Payment Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('recordDate', DateType::class, [
                'attr' => [
                    'placeholder' => 'Enter record date',
                    'data-date-format' => 'DD/MM/YYYY',
                    'autocomplete' => "off",
                ],
                'input'  => 'datetime',
                'format' => 'dd/MM/yyyy',
                'label' => 'Record Date',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'placeholder' => 'Enter status',
                'choices' => StockDividend::STATUS,
                'choice_label' => function ($choice, $key, $value) {
                    return ucwords($value);
                },
                'invalid_message' => 'Please, choose a valid status.',
            ])
            ->add('value', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter value',
                    'autocomplete' => "off",
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => StockDividend::class,
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
