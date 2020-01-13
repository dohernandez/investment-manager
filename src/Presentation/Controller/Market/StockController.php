<?php

namespace App\Presentation\Controller\Market;

use App\Presentation\View\Market\Stock\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stocks")
 */
final class StockController extends AbstractController
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
     * @Route("/", name="stock_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('stocks/index.html.twig', $this->indexView->index());
    }
}
