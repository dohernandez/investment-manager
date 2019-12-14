<?php

namespace App\Presentation\Controller\Transfer;

use App\Presentation\View\Transfer\IndexView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/transfers")
 */
final class TransferController extends AbstractController
{
    /**
     * @var IndexView
     */
    private $indexView;

    public function __construct(IndexView $indexView)
    {
        $this->indexView = $indexView;
    }

    /**
     * @Route("/", name="transfer_index", methods={"GET"})
     *
     * @return Response
     */
    public function index(): Response
    {
        return $this->render('transfer/index.html.twig', $this->indexView->index());
    }
}
