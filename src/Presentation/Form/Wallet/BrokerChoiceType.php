<?php

namespace App\Presentation\Form\Wallet;

use App\Application\Wallet\Repository\BrokerRepositoryInterface;
use App\Domain\Wallet\Broker;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class BrokerChoiceType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var BrokerRepositoryInterface
     */
    private $brokerRepository;

    /**
     * @var RouterInterface
     */
    private $resolver;

    public function __construct(BrokerRepositoryInterface $brokerRepository, RouterInterface $resolver)
    {
        $this->brokerRepository = $brokerRepository;
        $this->resolver = $resolver;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Force to remove existing View transformer to avoid transformer inconsistency
        $builder->resetViewTransformers();

        $builder->addModelTransformer(
            new ChoiceToBrokerTransformer($this->brokerRepository)
        );
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'invalid_message' => 'Broker not found',
                'class'           => Broker::class,
                'choices'         => [],
            ]
        );
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
        $class .= 'js-broker-autocomplete';
        $attr['class'] = $class;

        // Set autocomplete-url
        $attr['data-autocomplete-url'] = $this->resolver->generate('broker_list');

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
