<?php

namespace App\Application\Market\Handler;

use App\Application\Market\Command\AddStockInfo;
use App\Application\Market\Repository\StockInfoRepositoryInterface;
use App\Domain\Market\StockInfo;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class AddStockInfoHandler implements MessageHandlerInterface
{
    /**
     * @var StockInfoRepositoryInterface
     */
    private $stockInfoRepository;

    public function __construct(StockInfoRepositoryInterface $stockInfoRepository)
    {
        $this->stockInfoRepository = $stockInfoRepository;
    }

    public function __invoke(AddStockInfo $message)
    {
        $stockInfo = StockInfo::add($message->getName(), $message->getType());

        $this->stockInfoRepository->save($stockInfo);

        return $stockInfo;
    }
}
