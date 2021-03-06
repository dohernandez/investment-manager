<?php

namespace App\Presentation\Form\Market;

use App\Application\Market\Repository\ProjectionStockMarketRepositoryInterface;
use App\Domain\Market\StockMarket;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ChoiceToStockMarketTransformer implements DataTransformerInterface
{
    /**
     * @var ProjectionStockMarketRepositoryInterface
     */
    private $projectionStockMarketRepository;

    public function __construct(ProjectionStockMarketRepositoryInterface $stockMarketRepository)
    {
        $this->projectionStockMarketRepository = $stockMarketRepository;
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

        $stockMarket = $this->projectionStockMarketRepository->find($value);

        if (!$stockMarket) {
            throw new TransformationFailedException(
                sprintf(
                    'No market found with id "%s"',
                    $value
                )
            );
        }

        return $stockMarket;
    }
}
