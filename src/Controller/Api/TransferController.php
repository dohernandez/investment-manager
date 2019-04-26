<?php

namespace App\Controller\Api;

use App\Api;
use App\Entity;
use App\Form\TransferType;
use App\Repository\TransferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/**
 * @Route("/v1/transfer")
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
                'items' => $apiTransfers,
            ]
        );
    }

    /**
     * @Route("/", name="transfer_save", methods={"POST"}, options={"expose"=true})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function save(EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(TransferType::class);

        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            return $this->json(
                [
                    'message' => 'Invalid JSON',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        dump('data', $data);
        $form->submit($data);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                /** @var Transfer $transfer */
                $transfer = $form->getData();

                $em->persist($transfer);
                $em->flush();

                return $this->createApiResponse(
                    [
                        'item' => Api\Transfer::fromEntity($transfer),
                    ]
                );
            } else {
//                $iterator = $child->getErrors(true, true);
//
                $errors = [];
                foreach ($form->getErrors(true, true) as $error) {
                    dump($error->getCause()->getRoot());
                    $errors[$error->getCause()->getPropertyPath()][] = $error->getMessage();
                }

                dump($form->getErrors(true, true), $form, $errors);

                return $this->json(
                    [
                        'message' => 'fails $form->isValid()',
                    ],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }
        }

        dump($form->getErrors(true, true), $form);

        return $this->json(
            [
                'message' => 'fails $form->isSubmitted()',
            ],
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
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
