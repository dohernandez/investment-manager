<?php

namespace App\Controller;

use App\Entity\Wallet;
use App\Repository\WalletRepository;
use App\Twig\Parameters\WalletViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wallets")
 */
class WalletController extends AbstractController
{
    /**
     * @var WalletViewParameters
     */
    private $walletViewParameters;

    public function __construct(WalletViewParameters $walletViewParameters)
    {
        $this->walletViewParameters = $walletViewParameters;
    }

    /**
     * @Route("/", name="wallet_index", methods={"GET"})
     *
     * @param WalletRepository $repo
     *
     * @return Response
     */
    public function index(WalletRepository $repo): Response
    {
        $wallets = $repo->findAll();

        return $this->render('wallets/index.html.twig', $this->walletViewParameters->index($wallets));
    }

    /**
     * @Route("/{id}/dashboard", name="wallet_dashboard", methods={"GET"}, options={"expose"=true})
     *
     * @param Wallet $wallet
     *
     * @return Response
     */
    public function dashboard(Wallet $wallet): Response
    {
        return $this->render('wallets/dashboard.html.twig', $this->walletViewParameters->dashboard($wallet));
    }
}
