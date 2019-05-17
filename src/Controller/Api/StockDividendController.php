<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\StockDividendType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/stocks/{id}/dividend")
 */
class StockDividendController extends BaseController
{
    /**
     * @Route("/", name="stock_dividend_list", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Stock $stock
     *
     * @return JsonResponse
     */
    public function all(Entity\Stock $stock): JsonResponse
    {
        $apiStockDividends = [];

        foreach ($stock->getDividends() as $StockDividend) {
            $apiStockDividends[] = Api\StockDividend::fromEntity($StockDividend);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiStockDividends),
                'items' => $apiStockDividends,
            ]
        );
    }

    /**
     * @Route("/", name="stock_dividend_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Entity\Stock $stock
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Entity\Stock $stock, Request $request): Response
    {
        $stockDividend = new Entity\StockDividend();

        $form = $this->createForm(StockDividendType::class, $stockDividend);

        return $this->save($form, $em, $stock, $request);
    }

    /**
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Entity\Stock $stock
     * @param Request $request
     *
     * @return Response
     */
    protected function save(Form $form, EntityManagerInterface $em, Entity\Stock $stock, Request $request): Response
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
}
