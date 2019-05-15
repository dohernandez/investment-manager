<?php

namespace App\Form;

use App\Entity\StockInfo;
use App\Form\DataTransformer;
use App\Form\DataMapper;
use App\Repository\StockInfoRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class StockInfoChoiceType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var StockInfoRepository
     */
    private $stockInfoRepository;

    /**
     * @var RouterInterface
     */
    private $resolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(StockInfoRepository $stockInfoRepository, RouterInterface $resolver, LoggerInterface $logger)
    {
        $this->stockInfoRepository = $stockInfoRepository;
        $this->resolver = $resolver;
        $this->logger = $logger;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Force to remove existing View transformer to avoid transformer inconsistency
        $builder->resetViewTransformers();

        $builder->addModelTransformer(
            new DataTransformer\ChoiceToStockInfoTransformer(
                $this->stockInfoRepository,
                $options['type'],
                $this->logger
            )
        );
    }

    public function getParent()
    {
        return EntityType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invalid_message' => 'Stock info not found',
            'class' => StockInfo::class,
            'type' => 'type',
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
        $class .= 'js-stock-info-autocomplete';
        $attr['class'] = $class;

        // Set type
        $attr['data-type'] = $options['type'];

        // Set autocomplete-url
        $attr['data-autocomplete-url'] = $this->resolver->generate('stock_info_list');

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
