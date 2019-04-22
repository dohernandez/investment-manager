<?php

namespace App\Controller;

use App\Entity\Transfer;
use App\Form\TransferType;
use App\Repository\TransferRepository;
use App\Twig\Parameters\TransferViewParameters;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
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

    /**
     * @Route("/", name="transfer_save", methods={"POST"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     *
     * @return Response
     */
    public function save(EntityManagerInterface $em, Request $request): Response
    {
        $form = $this->createForm(TransferType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var Transfer $transfer */
            $transfer = $form->getData();

            $em->persist($transfer);
            $em->flush();

            $this->addFlash('success', sprintf(
               'Transfer %s created!',
               $transfer
            ));
        }

        return $this->redirectToRoute('transfer_index');
    }

    /**
     * @Route("/{id}", name="transfer_delete", methods={"DELETE"})
     *
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Transfer $transfer
     *
     * @return RedirectResponse
     */
    public function delete(EntityManagerInterface $em, Request $request, Transfer $transfer): RedirectResponse
    {
        if (!$transfer) {
            return $this->createNotFoundException('Transfer not found');
        }

        if ($this->isCsrfTokenValid('delete' . $transfer->getId(), $request->request->get('_token'))) {
            $em->remove($transfer);
            $em->flush();


            $this->addFlash('success', sprintf(
                'Transfer %s removed!',
                $transfer
            ));
        } else {
            $this->addFlash('error', sprintf(
                'Transfer %s can not remove! Csrf token is not valid.',
                $transfer
            ));
        }

        return $this->redirectToRoute('transfer_index');
    }
}
