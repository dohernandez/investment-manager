<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\WalletType;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets")
 */
class WalletController extends BaseController
{
    /**
     * @Route("/", name="wallet_list", methods={"GET"}, options={"expose"=true})
     *
     * @param WalletRepository $repo
     *
     * @return JsonResponse
     */
    public function all(WalletRepository $repo): JsonResponse
    {
        $wallets = $repo->findAll();

        $apiWallets = [];

        foreach ($wallets as $wallet) {
            $apiWallets[] = Api\Wallet::fromEntity($wallet);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiWallets),
                'items'       => $apiWallets,
            ]
        );
    }

    /**
     * @Route("/", name="wallet_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $wallet = new Entity\Wallet();

        $form = $this->createForm(WalletType::class, $wallet);

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

        /** @var Entity\Wallet $wallet */
        $wallet = $form->getData();

        $em->persist($wallet);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Wallet::fromEntity($wallet),
            ]
        );
    }

    /**
     * @Route("/{id}", name="wallet_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param Entity\Wallet $wallet
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\Wallet $wallet, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(WalletType::class, $wallet);

        return $this->save($form, $em, $request);
    }

    /**
     * @Route("/{id}", name="wallet_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Entity\Wallet $wallet
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Entity\Wallet $wallet): Response
    {
        if (!$wallet) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        $em->remove($wallet);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}", name="wallet_get", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Wallet $wallet
     *
     * @return JsonResponse
     */
    public function one(Entity\Wallet $wallet): JsonResponse
    {
        if (!$wallet) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\Wallet::fromEntity($wallet),
            ]
        );
    }

    /**
     * @Route("/{id}/dividend", name="wallet_dividend", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Wallet $wallet
     *
     * @return JsonResponse
     */
    public function dividend(Entity\Wallet $wallet): JsonResponse
    {
        if (!$wallet) {
            return $this->createApiErrorResponse('Wallet not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\WalletDividend::fromEntity($wallet),
            ]
        );
    }
}
