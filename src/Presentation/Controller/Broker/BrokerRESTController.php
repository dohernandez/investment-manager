<?php

namespace App\Presentation\Controller\Broker;

use App\Application\Broker\Command\ChangeBroker;
use App\Application\Broker\Command\RegisterBroker;
use App\Application\Broker\Repository\ProjectionBrokerRepositoryInterface;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Broker\CreateBrokerType;
use App\Presentation\Form\Broker\EditBrokerType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/brokers")
 */
final class BrokerRESTController extends RESTController
{
    /**
     * @Route("/", name="broker_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionBrokerRepositoryInterface $repo
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(ProjectionBrokerRepositoryInterface $repo, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $brokers = $repo->findAllMatching($query);
        } else {
            $brokers = $repo->findAll();
        }

        return $this->createApiResponse($brokers);
    }

    /**
     * @Route("/{id}", name="broker_get", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param ProjectionBrokerRepositoryInterface $projectionBrokerRepository
     *
     * @return JsonResponse
     */
    public function one(string $id, ProjectionBrokerRepositoryInterface $projectionBrokerRepository): JsonResponse
    {
        if ($id == '') {
            return $this->createApiErrorResponse('Broker not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $broker = $projectionBrokerRepository->find($id);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createApiResponse($broker);
    }

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

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new RegisterBroker(
                    $data['name'],
                    $data['site'],
                    $data['currency']
                );
            },
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="broker_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param string $id
     * @param Request $request
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function edit(string $id, Request $request, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(EditBrokerType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) use ($id) {
                return new ChangeBroker(
                    $id,
                    $data['name'],
                    $data['site'],
                    $data['currency']
                );
            }
        );
    }
}
