<?php

namespace App\Presentation\Controller\Transfer;

use App\Application\Transfer\Command\ChangeTransfer;
use App\Application\Transfer\Command\RegisterTransfer;
use App\Application\Transfer\Command\RemoveTransfer;
use App\Application\Transfer\Repository\ProjectionTransferRepositoryInterface;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Transfer\CreateTransferType;
use App\Presentation\Form\Transfer\EditTransferType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
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

    /**
     * @Route("/", name="transfer_new", methods={"POST"}, options={"expose"=true})
     *
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function new(
        Request $request,
        MessageBusInterface $bus
    ): Response {
        $form = $this->createForm(CreateTransferType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new RegisterTransfer(
                    $data['beneficiaryParty'],
                    $data['debtorParty'],
                    $data['amount'],
                    $data['date']
                );
            },
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="transfer_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param string $id
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function edit(string $id, Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(EditTransferType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) use ($id) {
                return new ChangeTransfer(
                    $id,
                    $data['beneficiaryParty'],
                    $data['debtorParty'],
                    $data['amount'],
                    $data['date']
                );
            }
        );
    }

    /**
     * @Route("/{id}", name="transfer_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param string $id
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function delete(string $id, MessageBusInterface $bus): Response
    {
        if ($id == '' || $id == null) {
            return $this->createApiErrorResponse('Transfer not found', Response::HTTP_NOT_FOUND);
        }

        $bus->dispatch(new RemoveTransfer($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
