<?php

namespace App\Presentation\Form\Market;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

final class LoadYahooQuoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'symbol',
                TextType::class,
                [
                    'attr'        => [
                        'placeholder'  => 'Enter symbol',
                        'autocomplete' => "off",
                    ],
                    'constraints' => [
                        new NotBlank()
                    ],
                ]
            )
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
