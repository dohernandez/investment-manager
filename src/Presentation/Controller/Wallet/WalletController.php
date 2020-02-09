<?php

namespace App\Presentation\Controller\Wallet;

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

    public function __construct(IndexView $indexView)
    {
        $this->indexView = $indexView;
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
}
