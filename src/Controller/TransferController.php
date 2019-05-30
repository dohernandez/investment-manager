<?php

namespace App\Controller;

use App\Repository\TransferRepository;
use App\Twig\Parameters\TransferViewParameters;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transfer")
 */
class TransferController extends AbstractController
{
    /**
     * @var TransferViewParameters
     */
    private $transferViewParameters;

    public function __construct(TransferViewParameters $transferViewParameters)
    {
        $this->transferViewParameters = $transferViewParameters;
    }

    /**
     * @Route("/", name="transfer_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('transfer/index.html.twig', $this->transferViewParameters->index());
    }
}
