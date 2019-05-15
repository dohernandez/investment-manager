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
 * @Route("/v1/stock_info")
 */
class StockInfoController extends BaseController
{
    /**
     * @Route("/", name="stock_info_list", methods={"GET"}, options={"expose"=true})
     *
     * @param StockInfoRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(StockInfoRepository $repo, Request $request): JsonResponse
    {
        $type = $request->query->get('t');
        $query = $request->query->get('q');

        if ($type !== null || $query !== null ) {
            if ($type !== null) {
                $stockInfos = $repo->findAllTypeMatching($type, $query);
            } else {
                $stockInfos = $repo->findAllMatching($query);
            }
        } else {
            $stockInfos = $repo->findAll();
        }

        $apiStockInfos = [];

        foreach ($stockInfos as $stockInfo) {
            $apiStockInfos[] = Api\StockInfo::fromEntity($stockInfo);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStockInfos),
                'items' => $apiStockInfos,
            ]
        );
    }
}
