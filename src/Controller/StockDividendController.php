<?php

namespace App\Controller;

use App\Entity\Stock;
use App\Twig\Parameters\StockDividendViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stocks/{id}/dividend")
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
     * @param Stock $stock
     *
     * @return Response
     */
    public function index(Stock $stock): Response
    {
        return $this->render('stocks_dividend/index.html.twig', $this->stockDividendViewParameters->index([], [
            'stock' => $stock
        ]));
    }
}
