<?php

namespace App\Presentation\Controller\Wallet;

use App\Application\Wallet\Command\GetPositionDividends;
use App\Application\Wallet\Command\GetWalletStatistics;
use App\Application\Wallet\Repository\ProjectionPositionRepositoryInterface;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Domain\Wallet\Position;
use App\Presentation\Controller\RESTController;
use ArrayIterator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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

        if ($status) {
            $positions = $repo->findAllByWalletStatus($walletId, $status);
        } else {
            $positions = $repo->findAllByWallet($walletId);
        }

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

    /**
     * @Route("/dividends", name="wallet_position_dividend_list", methods={"GET"}, options={"expose"=true})
     *
     * @param string $walletId
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return JsonResponse
     */
    public function dividends(string $walletId, Request $request, MessageBusInterface $bus): JsonResponse
    {
        $status = $request->query->get('s');

        if ($walletId == '' || $walletId == null) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        $result = $this->handle(new GetPositionDividends($walletId, $status), $bus);

        return $this->createApiResponse($result);
    }
}
