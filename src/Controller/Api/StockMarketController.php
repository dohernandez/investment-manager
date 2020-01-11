<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\StockMarketType;
use App\Repository\StockMarketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stock_markets")
 */
class StockMarketController extends BaseController
{
    /**
     *
     * @param StockMarketRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(StockMarketRepository $repo, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $stockMarkets = $repo->findAllMatching($query);
        } else {
            $stockMarkets = $repo->findAll();
        }

        $apiStockMarkets = [];

        foreach ($stockMarkets as $stockMarket) {
            $apiStockMarkets[] = Api\StockMarket::fromEntity($stockMarket);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStockMarkets),
                'items' => $apiStockMarkets,
            ]
        );
    }

    /**
     * @Route("/{id}", name="stock_market_get", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\StockMarket $stockMarket
     *
     * @return JsonResponse
     */
    public function one(Entity\StockMarket $stockMarket): JsonResponse
    {
        if (!$stockMarket) {
            return $this->createApiErrorResponse('StockMarket not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\StockMarket::fromEntity($stockMarket),
            ]
        );
    }

    /**
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $stockMarket = new Entity\StockMarket();

        $form = $this->createForm(StockMarketType::class, $stockMarket);

        return $this->save($form, $em, $request);
    }

    /**
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    protected function save(Form $form, EntityManagerInterface $em, Request $request): Response
    {
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

        /** @var Entity\StockMarket $stockMarket */
        $stockMarket = $form->getData();

        $em->persist($stockMarket);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\StockMarket::fromEntity($stockMarket),
            ]
        );
    }

    /**
     *
     * @param Entity\StockMarket $stockMarket
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\StockMarket $stockMarket, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(StockMarketType::class, $stockMarket);

        return $this->save($form, $em, $request);
    }

    /**
     *
     * @param EntityManagerInterface $em
     * @param Entity\StockMarket $stockMarket
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Entity\StockMarket $stockMarket): Response
    {
        if (!$stockMarket) {
            return $this->createApiErrorResponse('StockMarket not found', Response::HTTP_NOT_FOUND);
        }

        $em->remove($stockMarket);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
