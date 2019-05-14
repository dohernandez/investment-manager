<?php

namespace App\Form\DataTransformer;

use App\Entity\StockMarket;
use App\Repository\StockMarketRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToStockMarketTransformer implements DataTransformerInterface
{
    /**
     * @var StockMarketRepository
     */
    private $stockMarketRepository;

    public function __construct(StockMarketRepository $stockMarketRepository)
    {
        $this->stockMarketRepository = $stockMarketRepository;
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

        if (!$value instanceof StockMarket) {
            throw new \LogicException('The StockMarketChoiceType can only be used with StockMarket objects');
        }

        return (string) $value;
    }

    /**
     * @{@inheritdoc}
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $stockMarket = $this->stockMarketRepository->find($value);

        if (!$stockMarket) {
            throw new TransformationFailedException(sprintf(
                'No market found with id "%s"',
                $value
            ));
        }

        return $stockMarket;
    }
}
