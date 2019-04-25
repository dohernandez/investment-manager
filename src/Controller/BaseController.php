<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class BaseController  extends AbstractController
{
    /**
     * @param mixed $data Usually an object you want to serialize
     * @param int $statusCode
     * @param string|null $group Define how to serialize the object using Serializer\Annotation
     *
     * @return JsonResponse
     */
    protected function createApiResponse($data, $statusCode = 200, $group = null)
    {
        $context = [];

        if ( isset($group) ) {
            $context = ['group' => [$group]];
        }

        return $this->json($data, $statusCode, [], $context);
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
}
