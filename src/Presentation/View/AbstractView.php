<?php

namespace App\Presentation\View;

use App\Entity\Entity;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractView
{
    /**
     * @var string
     */
    protected $entityClass;

    /**
     * @var string
     */
    protected $prefixRoute;

    /**
     * @var string
     */
    protected $createFormTypeClass;

    /**
     * @var FormFactoryInterface
     */
    protected $form;

    /**
     * @var RouterInterface
     */
    protected $router;

    public function __construct(FormFactoryInterface $form, RouterInterface $router)
    {
        $this->form = $form;
        $this->router = $router;
    }

    /**
     * @return string
     */
    public function getEntityName(): string
    {
        $path = explode('\\', $this->entityClass);
        return array_pop($path);
    }

    /**
     * Generate the parameters to use when render index view.
     *
     * @param Entity[] $entities
     * @param array $context
     *
     * @return array
     */
    public function index(array $entities = [], array $context = []): array
    {
        $createForm = $this->form->create($this->createFormTypeClass);

        return [
                'entities' => $entities,
                'fields' => $this->getFields(),
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),
                'form' => $createForm->createView(),
            ] + $context;
    }

    abstract protected function getFields(): array;
}
