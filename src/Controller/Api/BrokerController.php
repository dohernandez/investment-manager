<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\BrokerType;
use App\Repository\BrokerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/brokers")
 */
class BrokerController extends BaseController
{
    /**
     * @Route("/", name="broker_list", methods={"GET"}, options={"expose"=true})
     *
     * @param BrokerRepository $repo
     *
     * @return JsonResponse
     */
    public function all(BrokerRepository $repo): JsonResponse
    {
        $brokers = $repo->findAll();

        $apiBrokers = [];

        foreach ($brokers as $broker) {
            $apiBrokers[] = Api\Broker::fromEntity($broker);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiBrokers),
                'items' => $apiBrokers,
            ]
        );
    }

    /**
     * @Route("/{id}", name="broker_get", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Broker $broker
     *
     * @return JsonResponse
     */
    public function one(Entity\Broker $broker): JsonResponse
    {
        if (!$broker) {
            return $this->createApiErrorResponse('Broker not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\Broker::fromEntity($broker),
            ]
        );
    }

    /**
     * @Route("/", name="broker_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $broker = new Entity\Broker();

        $form = $this->createForm(BrokerType::class, $broker);

        return $this->save($form, $em, $request);
    }

    /**
     * @param Form $form
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    protected function save(Form $form, EntityManagerInterface $em, Request $request): Response
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

        /** @var Broker $broker */
        $broker = $form->getData();

        $em->persist($broker);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Broker::fromEntity($broker),
            ]
        );
    }

    /**
     * @Route("/{id}", name="broker_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param Entity\Broker $broker
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\Broker $broker, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(BrokerType::class, $broker);

        return $this->save($form, $em, $request);
    }

    /**
     * @Route("/{id}", name="broker_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Entity\Broker $broker
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Request $request, Entity\Broker $broker): Response
    {
        if (!$broker) {
            return $this->createApiErrorResponse('Broker not found', Response::HTTP_NOT_FOUND);
        }

        $em->remove($broker);
        $em->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}