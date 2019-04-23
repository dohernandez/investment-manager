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
    private $form;

    /**
     * @var RouterInterface
     */
    private $router;

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
     * @return string
     */
    public function getEntityPrefixRoute(): string
    {
        return $this->prefixRoute;
    }

    /**
     * Generate the parameters to use when render index view.
     *
     * @param Entity[] $entities
     *
     * @return array
     */
    public function index(array $entities = []): array
    {
        $form = $this->form->create($this->newFormTypeClass);

        return [
                'entities' => $entities,
                'fields' => $this->getFields(),
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),
                'edit_route' => $this->getEntityPrefixRoute() . '_index',
                'delete_route' => $this->getEntityPrefixRoute() . '_delete',
                'form' => $form->createView(),
            ];
    }

    abstract protected function getFields(): array;
}
