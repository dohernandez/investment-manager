<?php

namespace App\Presentation\Controller\ExchangeMoney;

use App\Application\ExchangeMoney\Repository\ExchangeMoneyRepositoryInterface;
use App\Presentation\View\ExchangeMoney\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class ExchangeMoneyController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $indexView;

    public function __construct(IndexView $indexView)
    {
        $this->indexView = $indexView;
    }

    public function index(ExchangeMoneyRepositoryInterface $repo): Response
    {
        return $this->render('control-sidebar/exchange.html.twig', $this->indexView->index($repo->findAllLatest()));
    }
}
