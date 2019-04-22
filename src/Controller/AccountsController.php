<?php

namespace App\Controller;

use App\Entity\Account;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use App\Twig\Parameters\AccountViewParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/accounts")
 */
class AccountsController extends AbstractController
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

    /**
     * @Route("/", name="account_save", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function save(EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(AccountType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Account $account */
            $account = $form->getData();

            $em->persist($account);
            $em->flush();

            $this->addFlash('success', sprintf(
                'Account %s (%s) created!',
                $account->getName(),
                $account->getAccountNo()
            ));
        }

        return $this->redirectToRoute('account_index');
    }

    /**
     * @Route("/{id}", name="account_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Account $account
     *
     * @return RedirectResponse
     */
    public function delete(EntityManagerInterface $em, Request $request, Account $account): RedirectResponse
    {
        if (!$account) {
            throw $this->createNotFoundException('Account not found');
        }

        if ($this->isCsrfTokenValid('delete' . $account->getId(), $request->request->get('_token'))) {
            $em->remove($account);
            $em->flush();
        }

        return $this->redirectToRoute('account_index');
    }
}
