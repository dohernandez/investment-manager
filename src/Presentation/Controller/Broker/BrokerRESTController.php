<?php

namespace App\Presentation\Controller\Broker;

use App\Application\Broker\Command\RegisterBroker;
use App\Domain\Transfer\Transfer;
use App\Presentation\Controller\InvalidJsonRequestException;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Broker\CreateBrokerType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/brokers")
 */
final class BrokerRESTController extends RESTController
{
    /**
     * @Route("/", name="broker_new", methods={"POST"}, options={"expose"=true})
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
        $form = $this->createForm(CreateBrokerType::class);

        return $this->save($form, $request, $bus);
    }

    /**
     * @param Form $form
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    protected function save(
        Form $form,
        Request $request,
        MessageBusInterface $bus
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
            new RegisterBroker(
                $data['name'],
                $data['site'],
                $data['currency']
            )
        );

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);
        /** @var Transfer $transfer */
        $transfer = $handledStamp->getResult();

        return $this->createApiResponse($transfer, Response::HTTP_CREATED);
    }
}
