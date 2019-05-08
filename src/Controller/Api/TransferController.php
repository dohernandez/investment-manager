<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\TransferType;
use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/transfers")
 */
class TransferController extends BaseController
{
    /**
     * @Route("/", name="transfer_list", methods={"GET"}, options={"expose"=true})
     *
     * @param TransferRepository $repo
     *
     * @return JsonResponse
     */
    public function all(TransferRepository $repo): JsonResponse
    {
        $transfers = $repo->findAll();

        $apiTransfers = [];

        foreach ($transfers as $transfer) {
            $apiTransfers[] = Api\Transfer::fromEntity($transfer);
        }

        return $this->createApiResponse(
            [
                'total_count' => count($apiTransfers),
                'items' => $apiTransfers,
            ]
        );
    }

    /**
     * @Route("/{id}", name="transfer_get", methods={"GET"}, options={"expose"=true})
     *
     * @param Entity\Transfer $transfer
     *
     * @return JsonResponse
     */
    public function one(Entity\Transfer $transfer): JsonResponse
    {
        if (!$transfer) {
            return $this->createApiErrorResponse('Transfer not found', Response::HTTP_NOT_FOUND);
        }

        return $this->createApiResponse(
            [
                'item' => Api\Transfer::fromEntity($transfer),
            ]
        );
    }

    /**
     * @Route("/", name="transfer_new", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function new(EntityManagerInterface $em, Request $request): Response
    {
        $transfer = new Entity\Transfer();

        $form = $this->createForm(TransferType::class, $transfer);

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

        /** @var Transfer $transfer */
        $transfer = $form->getData();

        $em->persist($transfer);
        $em->flush();

        return $this->createApiResponse(
            [
                'item' => Api\Transfer::fromEntity($transfer),
            ]
        );
    }

    /**
     * @Route("/{id}", name="transfer_edit", methods={"PUT"}, options={"expose"=true})
     *
     * @param Entity\Transfer $transfer
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function edit(Entity\Transfer $transfer, EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(TransferType::class, $transfer);

        return $this->save($form, $em, $request);
    }

    /**
     * @Route("/{id}", name="transfer_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Transfer $transfer
     *
     * @return Response
     */
    public function delete(EntityManagerInterface $em, Request $request, Entity\Transfer $transfer): Response
    {
        if (!$transfer) {
            return $this->createApiErrorResponse('Transfer not found', Response::HTTP_NOT_FOUND);
        }

//        if ($this->isCsrfTokenValid('delete' . $transfer->getId(), $request->request->get('_token'))) {
        $em->remove($transfer);
        $em->flush();
//        } else {
//            return $this->json(
//                [
//                    'message' => 'Csrf token is not valid.',
//                ],
//                Response::HTTP_FORBIDDEN
//            );
//        }

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
