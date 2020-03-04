<?php

namespace App\Presentation\Controller\Market;

use App\Application\Market\Command\AddStockWithPrice;
use App\Application\Market\Command\DelistedStock;
use App\Application\Market\Command\LoadYahooQuote;
use App\Application\Market\Command\SyncStockDividends;
use App\Application\Market\Command\UpdateStockWithPrice;
use App\Application\Market\Repository\ProjectionStockRepositoryInterface;
use App\Application\Market\Repository\StockDividendRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Market\CreateStockType;
use App\Presentation\Form\Market\EditStockType;
use App\Presentation\Form\Market\LoadYahooQuoteType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks")
 */
final class StockRESTController extends RESTController
{
    /**
     * @Route("/", name="stock_list", methods={"GET"}, options={"expose"=true})
     *
     * @param Request $request
     * @param ProjectionStockRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function all(Request $request, ProjectionStockRepositoryInterface $repo): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $stocks = $repo->findAllMatching($query);
        } else {
            $stocks = $repo->findAll();
        }

        return $this->createApiResponse($stocks);
    }

    /**
     * @Route("/{id}", name="stock_get", methods={"GET"}, options={"expose"=true})
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
                    $data['lastChangePrice'],
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
     * @Route("/{id}", name="stock_edit", methods={"PUT"}, options={"expose"=true})
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
     * @Route("/{id}", name="stock_delete", methods={"DELETE"}, options={"expose"=true})
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

        $bus->dispatch(new DelistedStock($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/scraper/quote", name="stock_yahoo_scraper", methods={"GET"}, options={"expose"=true})
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

    /**
     * @Route("/{id}/sync/dividends", name="stock_dividend_sync", methods={"PUT"}, options={"expose"=true})
     *
     * @param string $id
     * @param MessageBusInterface $bus
     * @param Request $request
     *
     * @return Response
     */
    public function syncDividends(string $id, MessageBusInterface $bus, Request $request): Response
    {
        $envelope = $bus->dispatch(new SyncStockDividends($id));

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);

        return $this->createApiResponse($handledStamp->getResult(), Response::HTTP_OK);
    }

    /**
     * @Route("/{id}/dividends", name="stock_dividend_list", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param ProjectionStockRepositoryInterface $stockRepository
     * @param StockDividendRepositoryInterface $stockDividendRepository
     *
     * @return Response
     */
    public function dDividends(
        string $id,
        ProjectionStockRepositoryInterface $stockRepository,
        StockDividendRepositoryInterface $stockDividendRepository
    ): Response {
        $stock = $stockRepository->find($id);
        if (!$stock) {
            return $this->json(
                [
                    'message' => 'Resource not found',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        return $this->createApiResponse($stockDividendRepository->findAllByStock($stock), Response::HTTP_OK);
    }
}
