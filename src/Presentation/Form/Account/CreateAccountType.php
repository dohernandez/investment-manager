<?php

namespace App\Presentation\Form\Account;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CreateAccountType extends AbstractType
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
                    'attr' => [
                        'placeholder'  => 'Enter name',
                        'autocomplete' => "off",
                    ],
                    'constraints' => [
                        new NotBlank()
                    ],
                ]
            )
            ->add(
                'accountNo',
                TextType::class,
                [
                    'label' => 'IBAN',
                    'attr'  => [
                        'placeholder'  => 'Enter IBAN',
                        'autocomplete' => "off",
                    ],
                    'constraints' => [
                        new Iban()
                    ],
                ]
            )
            ->add(
                'type',
                HiddenType::class,
                [
                    'data' => 'iban',
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
