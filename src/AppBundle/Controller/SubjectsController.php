<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;

use AppBundle\Entity\Subjects;

/**
 * Subjects controller.
 *
 * @Route("/subjects")
 */
class SubjectsController extends Controller
{
    /**
     * Lists all Subjects entities.
     *
     * @Route("/", name="subjects")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('AppBundle:Subjects')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($subjects, $pagerHtml) = $this->paginator($queryBuilder, $request);
        
        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        return $this->render('subjects/index.html.twig', array(
            'subjects' => $subjects,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'totalOfRecordsString' => $totalOfRecordsString,

        ));
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter($queryBuilder, Request $request)
    {
        $session = $request->getSession();
        $filterForm = $this->createForm('AppBundle\Form\SubjectsFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('SubjectsControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->handleRequest($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('SubjectsControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('SubjectsControllerFilter')) {
                $filterData = $session->get('SubjectsControllerFilter');
                
                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }
                
                $filterForm = $this->createForm('AppBundle\Form\SubjectsFilterType', $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }


    /**
    * Get results from paginator and get paginator view.
    *
    */
    protected function paginator($queryBuilder, Request $request)
    {
        //sorting
        $sortCol = $queryBuilder->getRootAlias().'.'.$request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show' , 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }
        
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me, $request)
        {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('subjects', $requestParams);
        };

        // Paginator - view
        $view = new TwitterBootstrap3View();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => 'previous',
            'next_message' => 'next',
        ));

        return array($entities, $pagerHtml);
    }
    
    
    
    /*
     * Calculates the total of records string
     */
    protected function getTotalOfRecordsString($queryBuilder, $request) {
        $totalOfRecords = $queryBuilder->select('COUNT(e.id)')->getQuery()->getSingleScalarResult();
        $show = $request->get('pcg_show', 10);
        $page = $request->get('pcg_page', 1);

        $startRecord = ($show * ($page - 1)) + 1;
        $endRecord = $show * $page;

        if ($endRecord > $totalOfRecords) {
            $endRecord = $totalOfRecords;
        }
        return "Showing $startRecord - $endRecord of $totalOfRecords Records.";
    }
    
    

    /**
     * Displays a form to create a new Subjects entity.
     *
     * @Route("/new", name="subjects_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
    
        $subject = new Subjects();
        $form   = $this->createForm('AppBundle\Form\SubjectsType', $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush();
            
            $editLink = $this->generateUrl('subjects_edit', array('id' => $subject->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New subject was created successfully.</a>" );
            
            $nextAction=  $request->get('submit') == 'save' ? 'subjects' : 'subjects_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('subjects/new.html.twig', array(
            'subject' => $subject,
            'form'   => $form->createView(),
        ));
    }
    

    /**
     * Finds and displays a Subjects entity.
     *
     * @Route("/{id}", name="subjects_show")
     * @Method("GET")
     */
    public function showAction(Subjects $subject)
    {
        $deleteForm = $this->createDeleteForm($subject);
        return $this->render('subjects/show.html.twig', array(
            'subject' => $subject,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Displays a form to edit an existing Subjects entity.
     *
     * @Route("/{id}/edit", name="subjects_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Subjects $subject)
    {
        $deleteForm = $this->createDeleteForm($subject);
        $editForm = $this->createForm('AppBundle\Form\SubjectsType', $subject);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subject);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('subjects_edit', array('id' => $subject->getId()));
        }
        return $this->render('subjects/edit.html.twig', array(
            'subject' => $subject,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    

    /**
     * Deletes a Subjects entity.
     *
     * @Route("/{id}", name="subjects_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Subjects $subject)
    {
    
        $form = $this->createDeleteForm($subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($subject);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Subjects was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Subjects');
        }
        
        return $this->redirectToRoute('subjects');
    }
    
    /**
     * Creates a form to delete a Subjects entity.
     *
     * @param Subjects $subject The Subjects entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Subjects $subject)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('subjects_delete', array('id' => $subject->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
    
    /**
     * Delete Subjects by id
     *
     * @Route("/delete/{id}", name="subjects_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(Subjects $subject){
        $em = $this->getDoctrine()->getManager();
        
        try {
            $em->remove($subject);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Subjects was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Subjects');
        }

        return $this->redirect($this->generateUrl('subjects'));

    }
    

    /**
    * Bulk Action
    * @Route("/bulk-action/", name="subjects_bulk_action")
    * @Method("POST")
    */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('AppBundle:Subjects');

                foreach ($ids as $id) {
                    $subject = $repository->find($id);
                    $em->remove($subject);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'subjects was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the subjects ');
            }
        }

        return $this->redirect($this->generateUrl('subjects'));
    }
    

}
