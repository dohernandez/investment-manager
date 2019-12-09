<?php

namespace App\Presentation\Controller;

use ArrayAccess;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    protected function createApiResponse($data, $statusCode = 200, $context = [])
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
    protected function createApiErrorResponse($message, $statusCode = 500)
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

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}
