<?php

namespace App\Presentation\Controller\Account;

use App\Api;
use App\Application\Account\Storage\Finder;
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
     * @param Finder $finder
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(Finder $finder, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $accounts = $finder->allMatching($query);
        } else {
            $accounts = $finder->all();
        }

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
