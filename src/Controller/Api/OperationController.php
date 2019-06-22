<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\OperationType;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets/{_id}/operations")
 */
class OperationController extends BaseController
{
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * @Route("/", name="wallet_operation_list", methods={"GET"}, options={"expose"=true})
     *
     * @param int $_id
     *
     * @return JsonResponse
     */
    public function all(int $_id): JsonResponse
    {
        $wallet = $this->walletRepository->find($_id);

        $apiOperations = [];

        // get a new ArrayIterator
        $iterator = $wallet->getOperations()->getIterator();

        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(function ($first, $second) {
            return $first->getStock() && $first->getDateAt() < $second->getDateAt() ? 1 : -1;
        });

        foreach ($iterator as $Operation) {
            $apiOperations[] = Api\Operation::fromEntity($Operation);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiOperations),
                'items'       => $apiOperations,
            ]
        );
    }

    /**
     * @Route("/", name="wallet_operation_new", methods={"POST"}, options={"expose"=true})
     *
     * @param int $_id
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(int $_id, EntityManagerInterface $em, Request $request): Response
    {
        $wallet = $this->walletRepository->find($_id);

        $operation = new Entity\Operation();

        /** @var Form $form */
        $form = $this->createForm(OperationType::class, $operation);

        return $this->save($wallet, $form, $em, $request);
    }

    /**
     * @param Entity\Wallet $wallet
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    protected function save(Entity\Wallet $wallet, Form $form, EntityManagerInterface $em, Request $request): Response
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

        /** @var Entity\Operation $operation */
        $operation = $form->getData();
        $operation->setWallet($wallet);

        $em->persist($operation);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Operation::fromEntity($operation),
            ]
        );
    }
}
