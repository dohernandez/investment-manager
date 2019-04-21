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
        $deleteForms = [];

        /* @var Entity $entity */
        foreach ($entities as $entity) {
            $deleteForms[$entity->getId()] = $this->createDeleteFormView($entity);
        }

        $newForm = $this->form->create($this->newFormTypeClass)->createView();

        return [
                'entities' => $entities,
                'fields' => $this->getFields(),
                'entity_name' => trim(implode(' ', preg_split('/(?=[A-Z])/', $this->getEntityName()))),
                'edit_route' => $this->getEntityPrefixRoute() . '_index',
                'delete_route' => $this->getEntityPrefixRoute() . '_delete',
                'delete_forms' => $deleteForms,
                'new_form' => $newForm,
            ];
    }

    /**
     * Creates a form to delete an entity.
     *
     * @param Entity $entity
     *
     * @return FormView
     */
    protected function createDeleteFormView(Entity $entity): FormView
    {
        $routeName = $this->getEntityPrefixRoute() . '_delete';

        return $this->form->createBuilder(FormType::class)
            ->setAction($this->router->generate($routeName, ['id' => $entity->getId()]))
            ->setMethod('DELETE')
            ->getForm()
            ->createView()
        ;
    }

    abstract protected function getFields(): array;
}
