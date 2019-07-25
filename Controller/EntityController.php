<?php

namespace Softspring\AdminBundle\Controller;

use Jhg\DoctrinePagination\ORM\PaginatedRepository;
use Softspring\AccountBundle\Event\ViewEvent;
use Softspring\AdminBundle\Event\GetResponseEntityEvent;
use Softspring\AdminBundle\Event\GetResponseFormEvent;
use Softspring\AdminBundle\Form\AdminEntityCreateFormInterface;
use Softspring\AdminBundle\Form\AdminEntityDeleteFormInterface;
use Softspring\AdminBundle\Form\AdminEntityListFilterFormInterface;
use Softspring\AdminBundle\Form\AdminEntityUpdateFormInterface;
use Softspring\AdminBundle\Manager\AdminEntityManagerInterface;
use Softspring\ExtraBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
     * @var AdminEntityListFilterFormInterface
     */
    protected $listFilterForm;

    /**
     * @var AdminEntityCreateFormInterface
     */
    protected $createForm;

    /**
     * @var AdminEntityUpdateFormInterface
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
     * @param AdminEntityListFilterFormInterface $listFilterForm
     * @param AdminEntityCreateFormInterface $createForm
     * @param AdminEntityUpdateFormInterface $updateForm
     * @param AdminEntityDeleteFormInterface|null $deleteForm
     * @param array $config
     */
    public function __construct(AdminEntityManagerInterface $manager, EventDispatcherInterface $eventDispatcher, AdminEntityListFilterFormInterface $listFilterForm, AdminEntityCreateFormInterface $createForm, AdminEntityUpdateFormInterface $updateForm, ?AdminEntityDeleteFormInterface $deleteForm = null, array $config = [])
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
     *
     * @return Response
     */
    public function list(Request $request): Response
    {
        // additional fields for pagination and sorting
        $page = $this->listFilterForm->getPage($request);
        $rpp = $this->listFilterForm->getRpp($request);
        $orderSort = $this->listFilterForm->getOrder($request);

        // filter form
        $form = $this->createForm(get_class($this->listFilterForm))->handleRequest($request);
        $filters = $form->isSubmitted() && $form->isValid() ? array_filter($form->getData()) : [];

        // get results
        $repo = $this->manager->getRepository();
        if ($repo instanceof PaginatedRepository) {
            $entities = $repo->findPageBy($page, $rpp, $filters, $orderSort);
        } else {
            $entities = $repo->findBy($filters, $orderSort, $rpp, ($page-1)*$rpp);
        }

        // show view
        $viewData = new \ArrayObject([
            'entities' => $entities,
            'filterForm' => $form->createView(),
        ]);

        $this->eventDispatcher->dispatch(new ViewEvent($viewData), $this->config['list']['view_event_name']);

        if ($request->isXmlHttpRequest()) {
            return $this->render($this->config['list']['view_page'], $viewData->getArrayCopy());
        } else {
            return $this->render($this->config['list']['view'], $viewData->getArrayCopy());
        }
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function create(Request $request): Response
    {
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

                return $this->redirectToRoute('sfs_user_register_success');
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
}