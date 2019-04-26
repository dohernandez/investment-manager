<?php

namespace App\Controller;

use App\Entity\Transfer;
use App\Form\TransferType;
use App\Repository\TransferRepository;
use App\Twig\Parameters\TransferViewParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @param TransferRepository $repo
     *
     * @return Response
     */
    public function index(TransferRepository $repo): Response
    {
        $transfers = $repo->findAll();

        return $this->render('transfer/index.html.twig', $this->transferViewParameters->index($transfers));
    }
}
