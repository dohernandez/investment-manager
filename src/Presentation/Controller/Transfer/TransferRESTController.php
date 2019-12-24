<?php

namespace App\Presentation\Controller\Transfer;

use App\Application\Transfer\Command\RegisterTransfer;
use App\Application\Transfer\Repository\ProjectionTransferRepositoryInterface;
use App\Application\Transfer\Repository\TransferRepositoryInterface;
use App\Domain\Transfer\Transfer;
use App\Presentation\Controller\InvalidJsonRequestException;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Transfer\CreateTransferType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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
     * @param TransferRepositoryInterface $transferRepository
     *
     * @return Response
     */
    public function new(
        Request $request,
        MessageBusInterface $bus,
        TransferRepositoryInterface $transferRepository
    ): Response {
        $form = $this->createForm(CreateTransferType::class);

        return $this->save($form, $request, $bus, $transferRepository);
    }

    /**
     * @param Form $form
     * @param Request $request
     * @param MessageBusInterface $bus
     * @param TransferRepositoryInterface $transferRepository
     *
     * @return Response
     */
    protected function save(
        Form $form,
        Request $request,
        MessageBusInterface $bus,
        TransferRepositoryInterface $transferRepository
    ): Response {
        try {
            $data = $this->decodeRequestData($request);
            $form->submit($data);
        } catch (\Exception $e) {
            $status = $e instanceof InvalidJsonRequestException ?
                Response::HTTP_BAD_REQUEST :
                Response::HTTP_INTERNAL_SERVER_ERROR;

            return $this->json(
                [
                    'message' => $e->getMessage(),
                ],
                $status
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

        $data = $form->getData();
        $envelope = $bus->dispatch(
            new RegisterTransfer(
                $data['beneficiaryParty'],
                $data['debtorParty'],
                $data['amount'],
                $data['date']
            )
        );

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);
        /** @var Transfer $transfer */
        $transfer = $handledStamp->getResult();

        return $this->createApiResponse($transfer, Response::HTTP_CREATED);
    }
}
