<?php

namespace App\Presentation\Form\Market;

use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use App\Entity\StockInfo;
use Psr\Log\LoggerInterface;
use Symfony\Component\Form\DataTransformerInterface;

class ChoiceToStockInfoTransformer implements DataTransformerInterface
{
    /**
     * @var ProjectionStockInfoRepositoryInterface
     */
    private $stockInfoRepository;

    /**
     * @var string
     */
    private $type;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ProjectionStockInfoRepositoryInterface $stockInfoRepository, string $type, LoggerInterface $logger)
    {
        $this->stockInfoRepository = $stockInfoRepository;
        $this->type = $type;
        $this->logger = $logger;
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

        if (!$value instanceof StockInfo) {
            throw new \LogicException('The StockInfoChoiceType can only be used with StockInfo objects');
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

        $this->logger->debug('Find stock info with id', [
            'value' => $value,
            'type' => $this->type,
        ]);
        $stockInfo = $this->stockInfoRepository->find($value);

        if (!$stockInfo) {
            $this->logger->debug('Stock info not found. Creating a new', [
                'value' => $value,
                'type' => $this->type,
            ]);

            $stockInfo = new StockInfo();
            $stockInfo->setType($this->type);
            $stockInfo->setName($value);
        }

        return $stockInfo;
    }
}
