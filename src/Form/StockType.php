<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('market', StockMarketChoiceType::class, [
                'placeholder' => 'Choose a market'
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter name',
                    'autocomplete' => "off",
                ],
            ])
            ->add('symbol', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter symbol',
                    'autocomplete' => "off",
                ],
            ])
            ->add('value', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter value',
                    'autocomplete' => "off",
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'placeholder' => 'Enter description',
                    'style' => 'height: 200px;'
                ],
                'required' => false,
            ])
            ->add('type', StockInfoChoiceType::class, [
                'placeholder' => 'Choose type',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
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
