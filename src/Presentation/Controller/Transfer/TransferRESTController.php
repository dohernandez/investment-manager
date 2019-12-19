<?php

namespace App\Presentation\Controller\Transfer;

use App\Application\Trasnfer\Repository\ProjectionTransferRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Repository\TransferRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/transfers")
 */
final class TransferRESTController extends RESTController
{
    /**
     * @Route("/", name="transfer_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionTransferRepositoryInterface $repo
     *
     * @return JsonResponse
     */
    public function all(ProjectionTransferRepositoryInterface $repo): JsonResponse
    {
        return $this->createApiResponse($repo->findAll());
    }
}
