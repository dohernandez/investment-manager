<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter name',
                    'autocomplete' => "off",
                ],
            ])
            ->add('accountNo', TextType::class, [
                'label' => 'IBAN',
                'attr' => [
                    'placeholder' => 'Enter IBAN',
                    'autocomplete' => "off",
                ],
            ])
            ->add('alias', TextType::class, [
                'attr' => [
                    'placeholder' => 'Enter alias',
                    'autocomplete' => "off",
                ],
            ])
            ->add('type', HiddenType::class, [
                'data' => 'iban',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
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
