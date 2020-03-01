<?php

namespace App\Presentation\Controller\Wallet;

use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Presentation\Controller\RESTController;
use ArrayIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets/{walletId}/positions")
 */
final class WalletPositionRESTController extends RESTController
{
    /**
     * @Route("/", name="wallet_position_list", methods={"GET"}, options={"expose"=true})
     *
     * @param string $walletId
     * @param Request $request
     * @param ProjectionPositionRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function all(string $walletId, Request $request, ProjectionPositionRepositoryInterface $repo): JsonResponse
    {
        $status = $request->query->get('s');

        $positions = $repo->findAllByStatus($walletId, $status);
        $iterator = new ArrayIterator($positions);

        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(
            function ($first, $second) {
                /** @var Position $first */
                /** @var Position $second */
                return $first->getStock()->getName() < $second->getStock()->getName() ? 1 : -1;
            }
        );

        return $this->createApiResponse($iterator);
    }
}
