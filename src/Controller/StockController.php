<?php

namespace App\Controller;

use App\Repository\StockRepository;
use App\Twig\Parameters\StockViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/stocks")
 */
class StockController extends AbstractController
{
    /**
     * @var StockViewParameters
     */
    private $stockViewParameters;

    public function __construct(StockViewParameters $stockViewParameters)
    {
        $this->stockViewParameters = $stockViewParameters;
    }

    /**
     * @Route("/", name="stock_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('stocks/index.html.twig', $this->stockViewParameters->index([], [
            'buttons' => [
                [
                    'type' => 'warning',
                    'jsClass' => 'js-entity-edit-dividend-yield',
                    'icon' => 'fas fa-donate',
                ],
            ],
        ]));
    }
}
