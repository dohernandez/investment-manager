<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Repository\StockRepository;
use App\Twig\Parameters\StockDividendViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stocks/{_id}/dividend")
 */
class StockDividendController extends AbstractController
{
    /**
     * @var StockDividendViewParameters
     */
    private $stockDividendViewParameters;

    public function __construct(StockDividendViewParameters $stockDividendViewParameters)
    {
        $this->stockDividendViewParameters = $stockDividendViewParameters;
    }

    /**
     * @Route("/", name="stock_dividend_index", methods={"GET"}, options={"expose"=true})
     *
     * @param int $_id
     * @param StockRepository $stockRepository
     *
     * @return Response
     */
    public function index(int $_id, StockRepository $stockRepository): Response
    {
        $stock = $stockRepository->find($_id);

        return $this->render('stocks_dividend/index.html.twig', $this->stockDividendViewParameters->index([], [
            'stock' => $stock
        ]));
    }
}
