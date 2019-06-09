<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('symbol', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter symbol',
                    'autocomplete' => "off",
                ],
            ])
            ->add('yahoo_scrape', ButtonType::class, [
                'label' => 'Load finance.yahoo.com',
            ])
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter name',
                    'autocomplete' => "off",
                ],
            ])
            ->add('market', StockMarketChoiceType::class, [
                'placeholder' => 'Choose a market'
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
            ->add('sector', StockInfoChoiceType::class, [
                'placeholder' => 'Choose sector',
                'type' => 'sector',
                'required' => false,
            ])
            ->add('industry', StockInfoChoiceType::class, [
                'placeholder' => 'Choose industry',
                'type' => 'industry',
                'required' => false,
            ])
            // hidden inputs
            ->add('lastChangePrice', HiddenType::class, [
                'required' => false,
            ])
            ->add('peRatio', HiddenType::class, [
                'required' => false,
            ])
            ->add('preClose', HiddenType::class, [
                'required' => false,
            ])
            ->add('open', HiddenType::class, [
                'required' => false,
            ])
            ->add('dayLow', HiddenType::class, [
                'required' => false,
            ])
            ->add('dayHigh', HiddenType::class, [
                'required' => false,
            ])
            ->add('week52Low', HiddenType::class, [
                'required' => false,
            ])
            ->add('week52High', HiddenType::class, [
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
