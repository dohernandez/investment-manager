<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Twig\Parameters\AccountViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class AccountController extends AbstractController
{
    /**
     * @var AccountViewParameters
     */
    private $accountViewParameters;

    public function __construct(AccountViewParameters $accountViewParameters)
    {
        $this->accountViewParameters = $accountViewParameters;
    }

    /**
     * @Route("/", name="account_index", methods={"GET"})
     *
     * @param AccountRepository $repo
     *
     * @return Response
     */
    public function index(AccountRepository $repo): Response
    {
        $accounts = $repo->findAll();

        return $this->render('accounts/index.html.twig', $this->accountViewParameters->index($accounts));
    }
}
