<?php

namespace App\Presentation\Controller\Account;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Presentation\Controller\RESTController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/accounts")
 */
final class AccountRESTController extends RESTController
{
    /**
     * @Route("/", name="account_list", methods={"GET"}, options={"expose"=true})
     *
     * @param AccountRepositoryInterface $accountRepository
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(AccountRepositoryInterface $accountRepository, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $accounts = $accountRepository->allMatching($query);
        } else {
            $accounts = $accountRepository->findAll();
        }

        return $this->createApiResponse($accounts);
    }
}
