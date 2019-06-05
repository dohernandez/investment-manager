<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
//use App\Form\PositionType;
//use App\Message\PositionDeleted;
//use App\Message\PositionSaved;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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

    public function __construct(WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
    }

    /**
     * @Route("/", name="wallet_position_list", methods={"GET"}, options={"expose"=true})
     *
     * @param int $_id
     *
     * @return JsonResponse
     */
    public function all(int $_id): JsonResponse
    {
        $wallet = $this->walletRepository->find($_id);

        $apiPositions = [];

        // get a new ArrayIterator
        $iterator = $wallet->getPositions()->getIterator();

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
}
