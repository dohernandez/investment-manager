<?php

namespace App\Presentation\Controller\Wallet;

use App\Application\Market\Command\UpdateStockWithPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Wallet\Command\CreateWallet;
use App\Application\Wallet\Command\GetWalletStatistics;
use App\Application\Wallet\Repository\ProjectionWalletRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Market\EditStockType;
use App\Presentation\Form\Wallet\CreateWalletType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets")
 */
final class WalletRESTController extends RESTController
{
    /**
     * @Route("/", name="wallet_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionWalletRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function all(ProjectionWalletRepositoryInterface $repo): JsonResponse
    {
        return $this->createApiResponse($repo->findAll());
    }

    /**
     * @Route("/{id}", name="wallet_get", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param ProjectionStockRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function one(string $id, ProjectionStockRepositoryInterface $repo): JsonResponse
    {
        return $this->createApiResponse($repo->find($id));
    }

    /**
     * @Route("/", name="wallet_new", methods={"POST"}, options={"expose"=true})
     *
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function new(
        Request $request,
        MessageBusInterface $bus
    ): Response {
        $form = $this->createForm(CreateWalletType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new CreateWallet(
                    $data['name'],
                    $data['broker'],
                    $data['account']
                );
            },
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="wallet_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param string $id
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function edit(string $id, Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(EditStockType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) use ($id) {
                return new UpdateStockWithPrice(
                    $id,
                    $data['name'],
                    $data['yahooSymbol'],
                    $data['market'],
                    $data['value'],
                    $data['description'],
                    $data['type'],
                    $data['sector'],
                    $data['industry'],
                    $data['lastChangePrice'],
                    $data['preClose'],
                    $data['open'],
                    $data['peRatio'],
                    $data['dayLow'],
                    $data['dayHigh'],
                    $data['week52Low'],
                    $data['week52High']
                );
            }
        );
    }

    /**
     *
     * @param string $id
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function delete(string $id, MessageBusInterface $bus): Response
    {
        if ($id == '' || $id == null) {
            return $this->createApiErrorResponse('Stock not found', Response::HTTP_NOT_FOUND);
        }

//        $bus->dispatch(new RemoveStock($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/statistics", name="wallet_statistics", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function statistics(string $id, MessageBusInterface $bus): Response
    {
        if ($id == '' || $id == null) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        $result = $this->handle(new GetWalletStatistics($id), $bus);

        return $this->createApiResponse($result);
    }
}
