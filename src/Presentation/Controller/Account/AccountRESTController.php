<?php

namespace App\Presentation\Controller\Account;

use App\Application\Account\Command\CloseAccount;
use App\Application\Account\Command\OpenAccountCommand;
use App\Application\Account\Repository\ProjectionAccountRepositoryInterface;
use App\Infrastructure\Money\Currency;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Account\CreateAccountType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/accounts")
 */
final class AccountRESTController extends RESTController
{
    /**
     * @Route("/", name="account_list", methods={"GET"}, options={"expose"=true})
     *
     * @param ProjectionAccountRepositoryInterface $projectionAccountRepository
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(
        ProjectionAccountRepositoryInterface $projectionAccountRepository,
        Request $request
    ): JsonResponse {
        $query = $request->query->get('q');

        if ($query !== null) {
            $accounts = $projectionAccountRepository->findAllOpenMatching($query);
        } else {
            $accounts = $projectionAccountRepository->findAllOpen();
        }

        return $this->createApiResponse($accounts);
    }

    /**
     * @Route("/{id}", name="account_get", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param ProjectionAccountRepositoryInterface $projectionAccountRepository
     *
     * @return JsonResponse
     */
    public function one(string $id, ProjectionAccountRepositoryInterface $projectionAccountRepository): JsonResponse
    {
        if ($id == '') {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $account = $projectionAccountRepository->find($id);
        } catch (\Exception $e) {
            return $this->createApiErrorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->createApiResponse($account);
    }

    /**
     * @Route("/", name="account_new", methods={"POST"}, options={"expose"=true})
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
        $form = $this->createForm(CreateAccountType::class);

        return $this->dispatch(
            $form,
            $request,
            $bus,
            function ($data) {
                return new OpenAccountCommand(
                    $data['name'],
                    $data['type'],
                    $data['accountNo'],
                    Currency::eur()
                );
            },
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route("/{id}", name="account_delete", methods={"DELETE"}, options={"expose"=true})
     *
     * @param string $id
     * @param MessageBusInterface $bus
     *
     * @return Response
     */
    public function delete(string $id, MessageBusInterface $bus): Response
    {
        if ($id == '' || $id == null) {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        $bus->dispatch(new CloseAccount($id));

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
