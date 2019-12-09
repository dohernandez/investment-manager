<?php

namespace App\Presentation\Controller\Account;

use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Presentation\Controller\RESTController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    /**
     * @Route("/{id}", name="account_get", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return JsonResponse
     */
    public function one(string $id, AccountRepositoryInterface $accountRepository): JsonResponse
    {
        if ($id == '') {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $account = $accountRepository->find($id);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createApiResponse($account);
    }
}
