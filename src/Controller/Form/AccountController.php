<?php

namespace App\Controller\Form;

use App\Repository\AccountRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AccountController extends AbstractController
{
    /**
     * @Route("/v1/accounts/options", name="form_v1_account_options", methods={"GET"})
     *
     * @param AccountRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function optionsV1(AccountRepository $repo, Request $request): JsonResponse
    {
        $group = $request->query->get('group');
        $query = $request->query->get('q');

        return $this->json(
            [
                'items' => $repo->findAllMatching($query)
            ],
            Response::HTTP_OK,
            [],
            ['groups' => [$group],
        ]);
    }
}
