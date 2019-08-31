<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\StockType;
use App\Repository\StockRepository;
use App\Scrape\YahooStockScraper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks")
 */
class StockController extends BaseController
{
    /**
     * @Route("/", name="stock_list", methods={"GET"}, options={"expose"=true})
     *
     * @param StockRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(StockRepository $repo, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null ) {
            $stocks = $repo->findAllMatching($query);
        } else {
            $stocks = $repo->findAll();
        }

        $apiStocks = [];

        foreach ($stocks as $stock) {
            $apiStocks[] = Api\Stock::fromEntity($stock);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStocks),
                'items' => $apiStocks,
            ]
        );
    }

    /**
     * @Route("/{id}", name="stock_get", methods={"GET"}, options={"expose"=true}, requirements={"id":"\d+"})
     *
     * @param Entity\Stock $stock
     *
     * @return JsonResponse
     */
    public function one(Entity\Stock $stock): JsonResponse
    {
        if (!$stock) {
            return $this->createApiErrorResponse('Stock not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\Stock::fromEntity($stock),
            ]
        );
    }

    /**
     * @Route("/", name="stock_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $stock = new Entity\Stock();

        $form = $this->createForm(StockType::class, $stock);

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
            dump($e);
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

        /** @var Entity\Stock $stock */
        $stock = $form->getData();

        $em->persist($stock);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Stock::fromEntity($stock),
            ]
        );
    }

    /**
     * @Route("/{id}", name="stock_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param Entity\Stock $stock
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\Stock $stock, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(StockType::class, $stock);

        return $this->save($form, $em, $request);
    }

    /**
     * @Route("/{id}", name="stock_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Entity\Stock $stock
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Entity\Stock $stock): Response
    {
        if (!$stock) {
            return $this->createApiErrorResponse('Stock not found', Response::HTTP_NOT_FOUND);
        }

        $em->remove($stock);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/quote", name="stock_yahoo_scraper", methods={"GET"}, options={"expose"=true})
     *
     * @param YahooStockScraper $scraper
     * @param Request $request
     *
     * @return Response
     */
    public function loadYahooQuote(YahooStockScraper $scraper, Request $request): Response
    {
        $symbol = $request->query->get('symbol');
        if ($symbol === '') {
            return $this->json(
                [
                    'message' =>'symbol should not be empty',
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        $stock = new Entity\Stock();
        $stock->setSymbol($symbol);

        $scraper->updateFromQuote($stock);
        $scraper->updateFromProfile($stock);

        return $this->createApiResponse(
            [
                'item' => Api\Stock::fromEntity($stock),
            ]
        );
    }
}
