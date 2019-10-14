<?php

namespace Softspring\AdminBundle\Controller;

use Jhg\DoctrinePagination\ORM\PaginatedRepository;
use Softspring\AdminBundle\Event\GetResponseEntityEvent;
use Softspring\AdminBundle\Event\GetResponseFormEvent;
use Softspring\AdminBundle\Event\ViewEvent;
use Softspring\AdminBundle\Form\AdminEntityCreateFormInterface;
use Softspring\AdminBundle\Form\AdminEntityDeleteFormInterface;
use Softspring\AdminBundle\Form\AdminEntityListFilterFormInterface;
use Softspring\AdminBundle\Form\AdminEntityUpdateFormInterface;
use Softspring\AdminBundle\Manager\AdminEntityManagerInterface;
use Softspring\ExtraBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Entity CRUDL controller (CRUD+listing)
 */
class EntityController extends AbstractController
{
    /**
     * @var AdminEntityManagerInterface
     */
    protected $manager;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var AdminEntityListFilterFormInterface|null
     */
    protected $listFilterForm;

    /**
     * @var AdminEntityCreateFormInterface|null
     */
    protected $createForm;

    /**
     * @var AdminEntityUpdateFormInterface|null
     */
    protected $updateForm;

    /**
     * @var AdminEntityDeleteFormInterface|null
     */
    protected $deleteForm;

    /**
     * @var array
     */
    protected $config;

    /**
     * EntityController constructor.
     * @param AdminEntityManagerInterface $manager
     * @param EventDispatcherInterface $eventDispatcher
     * @param AdminEntityListFilterFormInterface|null $listFilterForm
     * @param AdminEntityCreateFormInterface|null $createForm
     * @param AdminEntityUpdateFormInterface|null $updateForm
     * @param AdminEntityDeleteFormInterface|null $deleteForm
     * @param array $config
     */
    public function __construct(AdminEntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher, ?AdminEntityListFilterFormInterface $listFilterForm = null, ?AdminEntityCreateFormInterface $createForm = null, ?AdminEntityUpdateFormInterface $updateForm = null, ?AdminEntityDeleteFormInterface $deleteForm = null, array $config = [])
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
        $this->listFilterForm = $listFilterForm;
        $this->createForm = $createForm;
        $this->updateForm = $updateForm;
        $this->deleteForm = $deleteForm;
        $this->config = $config;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
        if (!$this->createForm instanceof AdminEntityCreateFormInterface) {
            throw new \InvalidArgumentException(sprintf('Create form must be an instance of %s', AdminEntityCreateFormInterface::class));
        }

        $newEntity = $this->manager->createEntity();

        if ($response = $this->dispatchGetResponse($this->config['create']['initialize_event_name'], new GetResponseEntityEvent($newEntity, $request))) {
            return $response;
        }

        $form = $this->createForm(get_class($this->createForm), $newEntity, ['method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponse($this->config['create']['form_valid_event_name'], new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                $this->manager->saveEntity($newEntity);

                if ($response = $this->dispatchGetResponse($this->config['create']['success_event_name'], new GetResponseEntityEvent($newEntity, $request))) {
                    return $response;
                }

                return $this->redirect(!empty($this->config['create']['success_redirect_to']) ? $this->generateUrl($this->config['create']['success_redirect_to']) : '/');
            } else {
                if ($response = $this->dispatchGetResponse($this->config['create']['form_invalid_event_name'], new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'form' => $form->createView(),
        ]);

        $this->eventDispatcher->dispatch(new ViewEvent($viewData), $this->config['create']['view_event_name']);

        return $this->render($this->config['create']['view'], $viewData->getArrayCopy());
    }

    /**
     * @param string $entity
     * @param Request $request
     * @return Response
     */
    public function read(string $entity, Request $request): Response
    {
        // convert entity
        $entity = $this->manager->getRepository()->findOneBy([$this->config['read']['param_converter_key']=>$entity]);

        if (!$entity) {
            throw $this->createNotFoundException('Entity not found');
        }

        // show view
        $viewData = new \ArrayObject([
            'entity' => $entity,
        ]);

        $this->eventDispatcher->dispatch(new ViewEvent($viewData), $this->config['read']['view_event_name']);

        return $this->render($this->config['read']['view'], $viewData->getArrayCopy());
    }

    /**
     * @param string $entity
     * @param Request $request
     * @return Response
     */
    public function update(string $entity, Request $request): Response
    {
        $entity = $this->manager->getRepository()->findOneById($entity);

        if (!$this->updateForm instanceof AdminEntityUpdateFormInterface) {
            throw new \InvalidArgumentException(sprintf('Update form must be an instance of %s', AdminEntityUpdateFormInterface::class));
        }

        if ($response = $this->dispatchGetResponse($this->config['update']['initialize_event_name'], new GetResponseEntityEvent($entity, $request))) {
            return $response;
        }

        $form = $this->createForm(get_class($this->updateForm), $entity, ['method' => 'POST'])->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($response = $this->dispatchGetResponse($this->config['update']['form_valid_event_name'], new GetResponseFormEvent($form, $request))) {
                    return $response;
                }

                $this->manager->saveEntity($entity);

                if ($response = $this->dispatchGetResponse($this->config['update']['success_event_name'], new GetResponseEntityEvent($entity, $request))) {
                    return $response;
                }

                return $this->redirect(!empty($this->config['update']['success_redirect_to']) ? $this->generateUrl($this->config['update']['success_redirect_to']) : '/');
            } else {
                if ($response = $this->dispatchGetResponse($this->config['update']['form_invalid_event_name'], new GetResponseFormEvent($form, $request))) {
                    return $response;
                }
            }
        }

        // show view
        $viewData = new \ArrayObject([
            'form' => $form->createView(),
        ]);

        $this->eventDispatcher->dispatch(new ViewEvent($viewData), $this->config['update']['view_event_name']);

        return $this->render($this->config['update']['view'], $viewData->getArrayCopy());
    }

    /**
     * @param string $entity
     * @param Request $request
     * @return Response
     */
    public function delete(string $entity, Request $request): Response
    {
        throw $this->createNotFoundException('Not yet implemented');
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function list(Request $request): Response
    {
        $repo = $this->manager->getRepository();

        if ($this->listFilterForm) {
            if (!$this->listFilterForm instanceof AdminEntityListFilterFormInterface) {
                throw new \InvalidArgumentException(sprintf('List filter form must be an instance of %s', AdminEntityListFilterFormInterface::class));
            }

            // additional fields for pagination and sorting
            $page = $this->listFilterForm->getPage($request);
            $rpp = $this->listFilterForm->getRpp($request);
            $orderSort = $this->listFilterForm->getOrder($request);

            // filter form
            $form = $this->createForm(get_class($this->listFilterForm))->handleRequest($request);
            $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

            // get results
            if ($repo instanceof PaginatedRepository) {
                $entities = $repo->findPageBy($page, $rpp, $filters, $orderSort);
            } else {
                $entities = $repo->findBy($filters, $orderSort, $rpp, ($page - 1) * $rpp);
            }
        } else {
            $entities = $repo->findAll();
        }

        // show view
        $viewData = new \ArrayObject([
            'entities' => $entities,
            'filterForm' => $form->createView(),
            'read_route' => $this->config['list']['read_route'] ?? null,
        ]);

        $this->eventDispatcher->dispatch(new ViewEvent($viewData), $this->config['list']['view_event_name']);

        if ($request->isXmlHttpRequest()) {
            return $this->render($this->config['list']['view_page'], $viewData->getArrayCopy());
        } else {
            return $this->render($this->config['list']['view'], $viewData->getArrayCopy());
        }
    }
}