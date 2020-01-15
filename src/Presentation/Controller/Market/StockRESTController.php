<?php

namespace App\Presentation\Controller\Market;

use App\Application\Market\Command\AddStockWithPrice;
use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Transfer\Command\ChangeTransfer;
use App\Application\Transfer\Command\RemoveTransfer;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Market\CreateStockType;
use App\Presentation\Form\Market\LoadYahooQuoteType;
use App\Presentation\Form\Transfer\EditTransferType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks")
 */
final class StockRESTController extends RESTController
{
    /**
     * @Route("/", name="stock_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionStockRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function all(ProjectionStockRepositoryInterface $repo): JsonResponse
    {
        return $this->createApiResponse($repo->findAll());
    }

    /**
     * @Route("/", name="stock_new", methods={"POST"}, options={"expose"=true})
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
        $form = $this->createForm(CreateStockType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new AddStockWithPrice(
                    $data['name'],
                    $data['symbol'],
                    $data['yahooSymbol'],
                    $data['market'],
                    $data['value'],
                    $data['description'],
                    $data['type'],
                    $data['sector'],
                    $data['industry'],
                    $data['preClose'],
                    $data['open'],
                    $data['peRatio'],
                    $data['dayLow'],
                    $data['dayHigh'],
                    $data['week52Low'],
                    $data['week52High']
                );
            },
            Response::HTTP_CREATED
        );
    }

    /**
     *
     * @param string $id
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function edit(string $id, Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(EditTransferType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) use ($id) {
                return new ChangeTransfer(
                    $id,
                    $data['beneficiaryParty'],
                    $data['debtorParty'],
                    $data['amount'],
                    $data['date']
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
            return $this->createApiErrorResponse('Transfer not found', Response::HTTP_NOT_FOUND);
        }

        $bus->dispatch(new RemoveTransfer($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/quote", name="stock_yahoo_scraper", methods={"GET"}, options={"expose"=true})
     *
     * @param MessageBusInterface $bus
     * @param Request $request
     *
     * @return Response
     */
    public function loadYahooQuote(MessageBusInterface $bus, Request $request): Response
    {
        $form = $this->createForm(LoadYahooQuoteType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new LoadYahooQuote(
                    $data['symbol'],
                    $data['yahooSymbol']
                );
            }
        );
    }
}
