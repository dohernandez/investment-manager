<?php

namespace App\Presentation\Form\Wallet;

use App\Application\Wallet\Repository\StockRepositoryInterface;
use App\Domain\Wallet\Stock;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToStockTransformer implements DataTransformerInterface
{
    /**
     * @var StockRepositoryInterface
     */
    private $stockRepository;

    public function __construct(StockRepositoryInterface $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * @{@inheritdoc}
     */
    public function transform($value)
    {
        // TODO check if transform works when edit view is created
        if ($value === null) {
            return '';
        }

        if (!$value instanceof Stock) {
            throw new \LogicException('The StockChoiceType can only be used with Stock objects');
        }

        return $value->getTitle();
    }

    /**
     * @{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $stock = $this->stockRepository->find($value);

        if (!$stock) {
            throw new TransformationFailedException(
                sprintf(
                    'No stock found with id "%s"',
                    $value
                )
            );
        }

        return $stock;
    }
}
