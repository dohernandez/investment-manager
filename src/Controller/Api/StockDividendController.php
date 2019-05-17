<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\StockInfoType;
use App\Repository\StockInfoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks/{id}/dividend")
 */
class StockDividendController extends BaseController
{
    /**
     * @Route("/", name="stock_dividend_list", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Stock $stock
     *
     * @return JsonResponse
     */
    public function all(Entity\Stock $stock): JsonResponse
    {
        $apiStockDividends = [];

        foreach ($stock->getDividends() as $StockDividend) {
            $apiStockDividends[] = Api\StockInfo::fromEntity($StockDividend);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStockDividends),
                'items' => $apiStockDividends,
            ]
        );
    }
}
