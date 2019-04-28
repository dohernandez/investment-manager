<?php

namespace App\Form;

use App\Entity\Account;
use App\Form\DataTransformer;
use App\Form\DataMapper;
use App\Repository\AccountRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class AccountChoiceType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @var RouterInterface
     */
    private $resolver;

    public function __construct(AccountRepository $accountRepository, RouterInterface $resolver)
    {
        $this->accountRepository = $accountRepository;
        $this->resolver = $resolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Force to remove existing View transformer to avoid transformer inconsistency
        $builder->resetViewTransformers();

        $builder->addModelTransformer(
            new DataTransformer\ChoiceToAccountTransformer($this->accountRepository)
        );
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'Account not found',
            'class' => Account::class,
            'choices' => [],
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $attr = $view->vars['attr'];

        // Set style
        $style = isset($attr['style']) ? $attr['style'] . ';' : '';
        $style .= 'width: 100%';
        $attr['style'] = $style;

        // Set class
        $class = isset($attr['class']) ? $attr['class'] . ' ' : '';
        $class .= 'js-account-autocomplete';
        $attr['class'] = $class;

        // Set autocomplete-url
        $attr['data-autocomplete-url'] = $this->resolver->generate('account_list');

        $view->vars['attr'] = $attr;
    }

    /**
     * {@inheritdoc}
     */
    public function transform($data)
    {
        // Model data should not be transformed
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function reverseTransform($data)
    {
        return null === $data ? '' : $data;
    }

}
