<?php

namespace App\Controller;

use App\Repository\StockMarketRepository;
use App\Twig\Parameters\StockMarketViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stock_markets")
 */
class StockMarketController extends AbstractController
{
    /**
     * @var StockMarketViewParameters
     */
    private $stockMarketViewParameters;

    public function __construct(StockMarketViewParameters $stockMarketViewParameters)
    {
        $this->stockMarketViewParameters = $stockMarketViewParameters;
    }

    /**
     * @Route("/", name="stock_market_index", methods={"GET"})
     *
     * @param StockMarketRepository $repo
     *
     * @return Response
     */
    public function index(StockMarketRepository $repo): Response
    {
        $stockMarkets = $repo->findAll();

        return $this->render('stock_market/index.html.twig', $this->stockMarketViewParameters->index($stockMarkets));
    }
}
