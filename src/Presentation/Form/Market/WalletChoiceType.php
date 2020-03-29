<?php

namespace App\Presentation\Form\Market;

use App\Application\Market\Repository\WalletRepositoryInterface;
use App\Domain\Market\Wallet;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

final class WalletChoiceType extends AbstractType implements DataTransformerInterface
{
    /**
     * @var RouterInterface
     */
    private $resolver;

    public function __construct(RouterInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function buildView(
        FormView $view,
        FormInterface $form,
        array $options
    ) {
        $attr = $view->vars['attr'];

        // Set style
        $style = isset($attr['style']) ? $attr['style'] . ';' : '';
        $style .= 'width: 100%';
        $attr['style'] = $style;

        // Set class
        $class = isset($attr['class']) ? $attr['class'] . ' ' : '';
        $class .= 'js-wallet-autocomplete';
        $attr['class'] = $class;

        // Set autocomplete-url
        $attr['data-autocomplete-url'] = $this->resolver->generate('wallet_list');

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
