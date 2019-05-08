<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\AccountType;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/accounts")
 */
class AccountController extends BaseController
{
    /**
     * @Route("/", name="account_list", methods={"GET"}, options={"expose"=true})
     *
     * @param AccountRepository $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(AccountRepository $repo, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $accounts = $repo->findAllMatching($query);
        } else {
            $accounts = $repo->findAll();
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

    /**
     * @Route("/{id}", name="account_get", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Account $account
     *
     * @return JsonResponse
     */
    public function one(Entity\Account $account): JsonResponse
    {
        if (!$account) {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\Account::fromEntity($account),
            ]
        );
    }

    /**
     * @Route("/", name="account_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $account = new Entity\Account();

        $form = $this->createForm(AccountType::class, $account);

        return $this->save($form, $em, $request);
    }

    /**
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    protected function save(Form $form, EntityManagerInterface $em, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $this->json(
                [
                    'message' => 'Invalid JSON',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        try {
            $form->submit($data);
        } catch (\Exception $e) {
            return $this->json(
                [
                    'message' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        if (!$form->isValid()) {
            $errors = $this->getErrorsFromForm($form);

            return $this->createApiResponse(
                [
                    'errors' => $errors
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Entity\Account $account */
        $account = $form->getData();

        $em->persist($account);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Account::fromEntity($account),
            ]
        );
    }

    /**
     * @Route("/{id}", name="account_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param Entity\Account $account
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\Account $account, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(AccountType::class, $account);

        return $this->save($form, $em, $request);
    }

    /**
     * @Route("/{id}", name="account_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Entity\Account $account
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Entity\Account $account): Response
    {
        if (!$account) {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        $em->remove($account);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
