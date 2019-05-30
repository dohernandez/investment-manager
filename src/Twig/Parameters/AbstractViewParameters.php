<?php

namespace App\Twig\Parameters;

use App\Entity\Entity;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractViewParameters
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
    protected $newFormTypeClass;

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
        $form = $this->form->create($this->newFormTypeClass);

        return [
                'entities' => $entities,
                'fields' => $this->getFields(),
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),
                'form' => $form->createView(),
            ] + $context;
    }

    abstract protected function getFields(): array;
}
