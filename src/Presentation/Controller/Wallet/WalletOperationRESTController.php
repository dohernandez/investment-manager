<?php

namespace App\Presentation\Controller\Wallet;

use App\Application\Market\Command\UpdateStockWithPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Wallet\Command\CreateWallet;
use App\Application\Wallet\Command\GetWalletStatistics;
use App\Application\Wallet\Command\RegisterOperation;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Market\EditStockType;
use App\Presentation\Form\Wallet\CreateOperationType;
use App\Presentation\Form\Wallet\CreateWalletType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets/{id}/operations")
 */
final class WalletOperationRESTController extends RESTController
{
    /**
     * @Route("/", name="wallet_operation_new", methods={"POST"}, options={"expose"=true})
     *
     * @param string $id
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function newOperation(string $id, Request $request, MessageBusInterface $bus): Response
    {
        if ($id == '' || $id == null) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(CreateOperationType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) use ($id) {
                return new RegisterOperation(
                    $id,
                    $data['dateAt'],
                    $data['type'],
                    $data['value'],
                    $data['stock'],
                    $data['amount'],
                    $data['price'],
                    $data['priceChange'],
                    $data['priceChangeCommission'],
                    $data['commission']
                );
            }
        );
    }
}
