<?php

namespace App\Presentation\Form\Market;

use App\Domain\Market\StockDividend;
use App\Domain\Market\StockInfo;
use App\Presentation\Form\HiddenMoneyType;
use App\Presentation\Form\MoneyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class EditStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'yahooSymbol',
                TextType::class,
                [
                    'attr'     => [
                        'placeholder'  => 'Enter yahoo symbol',
                        'autocomplete' => "off",
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'yahoo_scrape',
                ButtonType::class,
                [
                    'label' => 'Load finance.yahoo.com',
                ]
            )
            ->add(
                'name',
                TextType::class,
                [
                    'attr'        => [
                        'placeholder'  => 'Enter name',
                        'autocomplete' => "off",
                    ],
                    'constraints' => [
                        new NotBlank()
                    ],
                ]
            )
            ->add(
                'market',
                StockMarketChoiceType::class,
                [
                    'placeholder' => 'Choose a market'
                ]
            )
            ->add(
                'value',
                MoneyType::class,
                [
                    'attr'     => [
                        'placeholder'  => 'Enter value',
                        'autocomplete' => "off",
                    ],
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'description',
                TextareaType::class,
                [
                    'attr'     => [
                        'placeholder' => 'Enter description',
                        'style'       => 'height: 200px;'
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'type',
                StockInfoChoiceType::class,
                [
                    'placeholder' => 'Choose type',
                    'type'        => StockInfo::TYPE,
                    'required'    => false,
                ]
            )
            ->add(
                'sector',
                StockInfoChoiceType::class,
                [
                    'placeholder' => 'Choose sector',
                    'type'        => StockInfo::SECTOR,
                    'required'    => false,
                ]
            )
            ->add(
                'industry',
                StockInfoChoiceType::class,
                [
                    'placeholder' => 'Choose industry',
                    'type'        => StockInfo::INDUSTRY,
                    'required'    => false,
                ]
            )
            ->add(
                'dividendFrequency',
                ChoiceType::class,
                [
                    'label' => 'Dividend Freq',
                    'placeholder' => 'Choose a dividend frequency',
                    'choices' => [
                        'monthly'   => StockDividend::FREQUENCY_MONTHlY,
                        'quarterly' => StockDividend::FREQUENCY_QUARTERLY,
                        'yearly'    => StockDividend::FREQUENCY_YEARLY,
                    ],
                    'choice_label' => function ($choice, $key, $value) {
                        return strtoupper($key);
                    },
                    'required' => false,
                ]
            )
            // hidden inputs
            ->add(
                'lastChangePrice',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'peRatio',
                HiddenType::class,
                [
                    'required' => false,
                ]
            )
            ->add(
                'preClose',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'open',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'dayLow',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'dayHigh',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'week52Low',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            )
            ->add(
                'week52High',
                HiddenMoneyType::class,
                [
                    'required' => false,
                    'divisor'  => 100,
                    'currency' => 'USD',
                ]
            );
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
