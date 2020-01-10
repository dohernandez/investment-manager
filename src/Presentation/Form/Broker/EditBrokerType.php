<?php

namespace App\Presentation\Form\Broker;

use App\Presentation\Form\CurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;

final class EditBrokerType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
                'site',
                TextType::class,
                [
                    'attr'        => [
                        'placeholder'  => 'Enter site',
                        'autocomplete' => "off",
                    ],
                    'constraints' => [
                        new NotBlank()
                    ],
                ]
            )
            ->add(
                'currency',
                CurrencyType::class,
                [
                    'constraints' => [
                        new NotNull()
                    ],
                ]
            );
    }

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
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return '';
    }
}
