<?php

namespace App\Presentation\Controller;

use ArrayAccess;
use Closure;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

abstract class RESTController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @param array $context
     *
     * @return JsonResponse
     */
    protected function createApiResponse($data, $statusCode = Response::HTTP_OK, $context = [])
    {
        if (is_array($data) || $data instanceof ArrayAccess) {
             $json = [
                 'total_count' => count($data),
                 'items' => $data
             ];
        } else {
            $json = [
                'item' => $data
            ];
        }

        return new JsonResponse($this->serializer->serialize($json, 'json'), $statusCode, [], true);
    }

    /**
     * Create an error json response with the following structure
     *
     * {
     *      "message"
     * }
     * @param string $message The error message.
     * @param int $statusCode
     *
     * @return JsonResponse
     */
    protected function createApiErrorResponse($message, $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return $this->json(
            [
                'message' => $message,
            ],
            $statusCode
        );
    }

    /**
     * Returns an associative array of validation errors
     *
     * {
     *     'firstName': 'This value is required',
     *     'subForm': {
     *         'someField': 'Invalid value'
     *     }
     * }
     *
     * @param FormInterface $form
     * @return array|string
     */
    protected function getErrorsFromForm(FormInterface $form)
    {
        foreach ($form->getErrors() as $error) {
            // only supporting 1 error per field
            // and not supporting a "field" with errors, that has more
            // fields with errors below it
            return $error->getMessage();
        }

        $errors = array();
        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childError = $this->getErrorsFromForm($childForm)) {
                    $errors[$childForm->getName()] = $childError;
                }
            }
        }

        return $errors;
    }

    public function decodeRequestData(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        if ($data === null) {
            throw new InvalidJsonRequestException('Invalid JSON');
        }

        return $data;
    }

    /**
     * @param Form $form
     * @param Request $request
     * @param MessageBusInterface $bus
     * @param Closure $message Function that create the message dispatched. Function received $data as unique argument.
     * @param int $statusCode
     *
     * @return Response
     */
    protected function dispatch(
        Form $form,
        Request $request,
        MessageBusInterface $bus,
        Closure $message,
        int $statusCode = Response::HTTP_OK
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
        $envelope = $bus->dispatch($message($data));

        // get the value that was returned by the last message handler
        $handledStamp = $envelope->last(HandledStamp::class);

        return $this->createApiResponse($handledStamp->getResult(), $statusCode);
    }
}
