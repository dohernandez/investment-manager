<?php

namespace App\Form;

use App\Form\DataMapper\MoneyMapper;
use App\VO\Money;
use Symfony\Component\Form\Extension\Core\Type\MoneyType as SymfonyMoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyType extends SymfonyMoneyType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addViewTransformer(new MoneyMapper(
                $options['scale'],
                $options['grouping'],
                $options['rounding_mode'],
                $options['divisor'],
                $options['currency'],
                $options['precision']
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'class' => Money::class,
            'precision' => 2,
        ]);
    }
}
