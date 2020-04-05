<?php

namespace App\Presentation\Controller\Market;

use App\Application\Market\Repository\ProjectionStockInfoRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stock_info")
 */
final class StockInfoRESTController
{
    /**
     * @Route("/", name="stock_info_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionStockInfoRepositoryInterface $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(ProjectionStockInfoRepositoryInterface $repo, Request $request): JsonResponse
    {
        $type = $request->query->get('t');
        $query = $request->query->get('q');

        if ($type !== null || $query !== null) {
            if ($type !== null) {
                $stockInfos = $repo->findAllTypeMatching($type, $query);
            } else {
                $stockInfos = $repo->findAllMatching($query);
            }
        } else {
            $stockInfos = $repo->findAll();
        }

        return $this->createApiResponse($stockInfos);
    }
}
