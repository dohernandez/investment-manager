<?php

namespace App\Presentation\Form\Wallet;

use App\Domain\Wallet\Position;
use App\Presentation\Form\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PositionDividendRetentionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('dividendRetention', MoneyType::class, [
                'attr' => [
                    'placeholder' => 'Enter value',
                    'autocomplete' => "off",
                ],
                'divisor' => 10000,
                'currency' => 'USD',
                'precision' => '4',
                'scale' => 4,
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
