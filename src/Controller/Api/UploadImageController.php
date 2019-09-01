<?php

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/v1/upload")
 */
class UploadImageController extends BaseController
{
    /**
     * @Route("/image", name="upload_note_image", methods={"POST"}, options={"expose"=true})
     *
     * @return JsonResponse
     */
    public function image(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('upload');
        if (!$file) {
             return $this->json(
                [
                    'uploaded' => 0,
                    'error' => [
                        'message' => 'Missing file',
                    ]
                ],
                Response::HTTP_BAD_REQUEST
            );;
        }

        $filename = md5(uniqid()) . '.' . $file->guessClientExtension();

        $file->move($this->getParameter('upload_note_dir'), $filename);

        return $this->json([
            'uploaded' => 1,
            'fileName' => $filename,
            'url' => '/upload/notes/files/' . $filename,
        ]);
    }
}
