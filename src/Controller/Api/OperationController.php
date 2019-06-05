<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
//use App\Form\OperationType;
//use App\Message\OperationDeleted;
//use App\Message\OperationSaved;
use App\Repository\WalletRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/wallets/{_id}/operation")
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
            return $first->getStock()->getName() < $second->getStock()->getName() ? 1 : -1;
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
}
