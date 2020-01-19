<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\StockDividendType;
use App\Repository\StockRepository;
use App\Service\StockDividendsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks/{_id}/dividends")
 */
class StockDividendController extends BaseController
{
    /**
     * @var StockRepository
     */
    private $stockRepository;

    public function __construct(StockRepository $stockRepository)
    {
        $this->stockRepository = $stockRepository;
    }

    /**
     * @Route("/", name="stock_dividend_list", methods={"GET"}, options={"expose"=true})
     *
     * @param int $_id
     *
     * @return JsonResponse
     */
    public function all(int $_id): JsonResponse
    {
        $stock = $this->stockRepository->find($_id);

        $apiStockDividends = [];

        // get a new ArrayIterator
        $iterator = $stock->getDividends()->getIterator();

        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(function ($first, $second) {
            return $first->getExDate() < $second->getExDate() ? 1 : -1;
        });

        foreach ($iterator as $StockDividend) {
            $apiStockDividends[] = Api\StockDividend::fromEntity($StockDividend);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStockDividends),
                'items'       => $apiStockDividends,
            ]
        );
    }

    /**
     * @Route("/{id}", name="stock_dividend_get", methods={"GET"}, options={"expose"=true}, requirements={"id":"\d+"})
     *
     * @param int $_id
     * @param Entity\StockDividend $stockDividend
     *
     * @return JsonResponse
     */
    public function one(int $_id, Entity\StockDividend $stockDividend): JsonResponse
    {
        $stock = $this->stockRepository->find($_id);

        if (!$stock->getDividends()->contains($stockDividend)) {
            return $this->json(
                [
                    'message' => 'Resource does not belongs to the entity',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if (!$stockDividend) {
            return $this->createApiErrorResponse('StockDividend not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\StockDividend::fromEntity($stockDividend),
            ]
        );
    }

    /**
     * @Route("/", name="stock_dividend_new", methods={"POST"}, options={"expose"=true})
     *
     * @param int $_id
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(int $_id, EntityManagerInterface $em, Request $request, MessageBusInterface $bus): Response
    {
        $stock = $this->stockRepository->find($_id);
        $stockDividend = new Entity\StockDividend();

        $form = $this->createForm(StockDividendType::class, $stockDividend);

        return $this->save($form, $em, $stock, $request, $bus);
    }

    /**
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Entity\Stock $stock
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    protected function save(
        Form $form,
        EntityManagerInterface $em,
        Entity\Stock $stock,
        Request $request,
        MessageBusInterface $bus
    ): Response {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $this->json(
                [
                    'message' => 'Invalid JSON',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $form->submit($data);
        } catch (\Exception $e) {
            return $this->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);

            return $this->createApiResponse(
                [
                    'errors' => $errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Entity\StockDividend $stockDividend */
        $stockDividend = $form->getData();
        $stockDividend->setStock($stock);

        $em->persist($stockDividend);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\StockDividend::fromEntity($stockDividend),
            ]
        );
    }

    /**
     * @Route("/{id}", name="stock_dividend_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param int $_id
     * @param Entity\StockDividend $stockDividend
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function edit(
        int $_id,
        Entity\StockDividend $stockDividend,
        EntityManagerInterface $em,
        Request $request,
        MessageBusInterface $bus
    ): Response {
        $stock = $this->stockRepository->find($_id);

        if (!$stock->getDividends()->contains($stockDividend)) {
            return $this->json(
                [
                    'message' => 'Resource does not belongs to the entity',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $form = $this->createForm(StockDividendType::class, $stockDividend);

        return $this->save($form, $em, $stock, $request, $bus);
    }

    /**
     * @Route("/{id}", name="stock_dividend_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param int $_id
     * @param Entity\StockDividend $stockDividend
     * @param EntityManagerInterface $em
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function delete(
        int $_id,
        Entity\StockDividend $stockDividend,
        EntityManagerInterface $em,
        MessageBusInterface $bus
    ): Response {
        if (!$stockDividend) {
            return $this->createApiErrorResponse('Stock dividend not found', Response::HTTP_NOT_FOUND);
        }

        $stock = $this->stockRepository->find($_id);

        if (!$stock->getDividends()->contains($stockDividend)) {
            return $this->json(
                [
                    'message' => 'Resource does not belongs to the entity',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $stockDividend->setStock(null);

        $em->remove($stockDividend);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     *
     * @param StockDividendsService $dividendService
     * @param EntityManagerInterface $em
     * @param int $_id
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function sync(
        StockDividendsService $dividendService,
        EntityManagerInterface $em,
        int $_id
    ): JsonResponse {
        $stock = $this->stockRepository->find($_id);
        if (!$stock) {
            return $this->createApiErrorResponse('Stock not found', Response::HTTP_NOT_FOUND);
        }

        $stock->removeProjectedAndAnnouncedDividends();

        $stockDividends = $dividendService->getStockDividends($stock);

        foreach ($stockDividends as $stockDividend) {
            $stock->addDividend($stockDividend);
        }

        $em->persist($stock);
        $em->flush();

        $apiStockDividends = [];
        foreach ($stock->getDividends() as $StockDividend) {
            $apiStockDividends[] = Api\StockDividend::fromEntity($StockDividend);
        }

        return $this->createApiResponse(
            [
                'total_count' => $stock->getDividends()->count(),
                'items'       => $apiStockDividends,
            ]
        );
    }
}
