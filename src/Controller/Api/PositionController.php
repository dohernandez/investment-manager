<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\DividendRetentionType;
use App\Repository\PositionRepository;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets/{_id}/positions")
 */
class PositionController extends BaseController
{
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    /**
     * @var PositionRepository
     */
    private $positionRepository;

    public function __construct(WalletRepository $walletRepository, PositionRepository $positionRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->positionRepository = $positionRepository;
    }

    /**
     * @Route("/", name="wallet_position_list", methods={"GET"}, options={"expose"=true})
     *
     * @param int $_id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(int $_id, Request $request): JsonResponse
    {
        $wallet = $this->walletRepository->find($_id);

        $apiPositions = [];

        $status = $request->query->get('s');

        // get a new ArrayIterator
        $iterator = $wallet->getPositions($status)->getIterator();

        // define ordering closure, using preferred comparison method/field
        $iterator->uasort(function ($first, $second) {
            return $first->getStock()->getName() < $second->getStock()->getName() ? 1 : -1;
        });

        foreach ($iterator as $position) {
            $apiPositions[] = Api\Position::fromEntity($position);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiPositions),
                'items'       => $apiPositions,
            ]
        );
    }

    /**
     * @Route("/{id}", name="wallet_position_dividend_retention", methods={"PATCH"}, options={"expose"=true})
     *
     * @param int $_id
     * @param int $id
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function updateRetentionType(int $_id, int $id, EntityManagerInterface $em, Request $request): Response
    {
        $position = $this->positionRepository->getByIdAndWalletId($id, $_id);

        if ($position === null) {
            return $this->json(
                [
                    'message' => 'Can not access to the position resource',
                ],
                Response::HTTP_FORBIDDEN
            );
        }

        $form = $this->createForm(DividendRetentionType::class, $position);

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
            dump($e);
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

        /** @var Entity\Position $position */
        $position = $form->getData();

        dump($position);

//        $em->persist($position);
//        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Position::fromEntity($position),
            ]
        );
    }
}
