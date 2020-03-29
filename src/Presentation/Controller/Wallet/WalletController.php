<?php

namespace App\Presentation\Controller\Wallet;

use App\Presentation\View\Wallet\DashboardView;
use App\Presentation\View\Wallet\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wallets")
 */
final class WalletController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $indexView;
    /**
     * @var DashboardView
     */
    private $dashboardView;

    public function __construct(IndexView $indexView, DashboardView $dashboardView)
    {
        $this->indexView = $indexView;
        $this->dashboardView = $dashboardView;
    }

    /**
     * @Route("/", name="wallet_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('wallets/index.html.twig', $this->indexView->index());
    }

    /**
     * @Route("/{id}/dashboard", name="wallet_dashboard", methods={"GET"}, options={"expose"=true})
     *
     *
     * @param string $id
     *
     * @return Response
     */
    public function dashboard(string $id): Response
    {
        return $this->render('wallets/dashboard_2.html.twig', $this->dashboardView->index($id));
    }
}
