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
}
