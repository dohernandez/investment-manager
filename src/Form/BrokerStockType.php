<?php

namespace App\Form;

use App\Entity\Stock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BrokerStockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
//        $builder
//            ->add('name', TextType::class, [
//                'attr' => [
//                    'placeholder' => 'Enter name',
//                    'autocomplete' => "off",
//                ],
//            ])
//            ->add('site', TextType::class, [
//                'attr' => [
//                    'placeholder' => 'Enter site',
//                    'autocomplete' => "off",
//                ],
//                'required' => false,
//            ])
//            ->add('account', AccountChoiceType::class, [
//                'placeholder' => 'Choose a account',
//            ])
//        ;
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
