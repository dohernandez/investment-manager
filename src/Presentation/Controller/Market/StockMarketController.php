<?php

namespace App\Presentation\Controller\Market;

use App\Presentation\View\Market\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stock_markets")
 */
final class StockMarketController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $indexView;

    public function __construct(IndexView $indexView)
    {
        $this->indexView = $indexView;
    }

    /**
     * @Route("/", name="stock_market_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('stock_market/index.html.twig', $this->indexView->index());
    }
}
