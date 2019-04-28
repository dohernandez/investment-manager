<?php

namespace App\Controller\Api;

use App\Api;
use App\Repository\AccountRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/accounts")
 */
class AccountController extends BaseController
{
    /**
     * @Route("/", name="account_list", methods={"GET"})
     *
     * @param AccountRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function optionsV1(AccountRepository $repo, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        $accounts = $repo->findAllMatching($query);

        $apiAccounts = [];

        foreach ($accounts as $account) {
            $apiAccounts[] = Api\Account::fromEntity($account);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiAccounts),
                'items' => $apiAccounts,
            ]
        );
    }
}
