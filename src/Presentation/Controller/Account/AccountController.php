<?php

namespace App\Presentation\Controller\Account;

use App\Presentation\View\Account\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
final class AccountController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $accountIndexView;

    public function __construct(IndexView $accountIndexView)
    {
        $this->accountIndexView = $accountIndexView;
    }

    /**
     * @Route("/", name="account_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('accounts/index.html.twig', $this->accountIndexView->index());
    }
}
