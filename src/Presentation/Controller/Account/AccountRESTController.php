<?php

namespace App\Presentation\Controller\Account;

use App\Application\Account\Command\CloseAccount;
use App\Application\Account\Command\OpenAccountCommand;
use App\Application\Account\Repository\AccountRepositoryInterface;
use App\Domain\Account\Account;
use App\Infrastructure\Money\Currency;
use App\Presentation\Controller\InvalidJsonRequestException;
use App\Presentation\Controller\RESTController;
use App\Presentation\Form\Account\CreateAccountType;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/accounts")
 */
final class AccountRESTController extends RESTController
{
    /**
     * @Route("/", name="account_list", methods={"GET"}, options={"expose"=true})
     *
     * @param AccountRepositoryInterface $accountRepository
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function all(AccountRepositoryInterface $accountRepository, Request $request): JsonResponse
    {
        $query = $request->query->get('q');

        if ($query !== null) {
            $accounts = $accountRepository->allMatching($query);
        } else {
            $accounts = $accountRepository->findAll();
        }

        return $this->createApiResponse($accounts);
    }

    /**
     * @Route("/{id}", name="account_get", methods={"GET"}, options={"expose"=true})
     *
     * @param string $id
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return JsonResponse
     */
    public function one(string $id, AccountRepositoryInterface $accountRepository): JsonResponse
    {
        if ($id == '') {
            return $this->createApiErrorResponse('Account not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $account = $accountRepository->find($id);
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
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return Response
     */
    public function new(
        Request $request,
        MessageBusInterface $bus,
        AccountRepositoryInterface $accountRepository
    ): Response {
        $form = $this->createForm(CreateAccountType::class);

        return $this->save($form, $request, $bus, $accountRepository);
    }

    /**
     * @param Form $form
     * @param Request $request
     * @param MessageBusInterface $bus
     * @param AccountRepositoryInterface $accountRepository
     *
     * @return Response
     */
    protected function save(
        Form $form,
        Request $request,
        MessageBusInterface $bus,
        AccountRepositoryInterface $accountRepository
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

        $envelope = $bus->dispatch(
            new OpenAccountCommand(
                $data['name'],
                $data['type'],
                $data['accountNo'],
                Currency::eur()
            )
        );

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);
        /** @var Account $account */
        $account = $handledStamp->getResult();

        return $this->createApiResponse($account, Response::HTTP_CREATED);
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
