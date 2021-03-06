<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrap3View;
use AppBundle\Form\FilesType;
use Symfony\symfony\src\Symfony\Component\Routing;
use AppBundle\Entity\Files;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\FOSRestController;
use AppBundle\Entity\History;

/**
 * Files controller.
 *
 * @Route("/files")
 */
class FilesController extends Controller
{
    /**
     * Lists all Files entities.
     *
     * @Route("/", name="files")
     * @Method("GET")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('AppBundle:Files')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($files, $pagerHtml) = $this->paginator($queryBuilder, $request);

        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        return $this->render('files/index.html.twig', array(
            'files' => $files,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
            'totalOfRecordsString' => $totalOfRecordsString,
        ));
    }

    
        /**
     * Lists all Files entities.
     *
     * @Route("/", name="statistics")
     * @Method("GET")
     */
    public function getAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('AppBundle:Files')->createQueryBuilder('e');

        list($filterForm, $queryBuilder) = $this->filter($queryBuilder, $request);
        list($files, $pagerHtml) = $this->paginator($queryBuilder, $request);

        $totalOfRecordsString = $this->getTotalOfRecordsString($queryBuilder, $request);

        return $this->render('files/index.html.twig', array(
            'files' => $files,
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
        $filterForm = $this->createForm('AppBundle\Form\FilesFilterType');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('FilesControllerFilter');
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
                $session->set('FilesControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('FilesControllerFilter')) {
                $filterData = $session->get('FilesControllerFilter');

                foreach ($filterData as $key => $filter) { //fix for entityFilterType that is loaded from session
                    if (is_object($filter)) {
                        $filterData[$key] = $queryBuilder->getEntityManager()->merge($filter);
                    }
                }

                $filterForm = $this->createForm('AppBundle\Form\FilesFilterType', $filterData);
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
        $sortCol = $queryBuilder->getRootAlias() . '.' . $request->get('pcg_sort_col', 'id');
        $queryBuilder->orderBy($sortCol, $request->get('pcg_sort_order', 'desc'));
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($request->get('pcg_show', 10));

        try {
            $pagerfanta->setCurrentPage($request->get('pcg_page', 1));
        } catch (\Pagerfanta\Exception\OutOfRangeCurrentPageException $ex) {
            $pagerfanta->setCurrentPage(1);
        }

        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function ($page) use ($me, $request) {
            $requestParams = $request->query->all();
            $requestParams['pcg_page'] = $page;
            return $me->generateUrl('files', $requestParams);
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
    protected function getTotalOfRecordsString($queryBuilder, $request)
    {
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
     * Displays a form to create a new Files entity.
     *
     * @Route("/new", name="files_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $file = new Files();
        $form = $this->createForm(FilesType::class, $file);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $brochure = $file->getBrochure();
            $fileName = $this->generateUniqueFileName() . '.' . $brochure->guessExtension();
            // Move the file to the directory whe   re brochures are stored
            try {
                $brochure->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $file->setBrochure($fileName);

            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();
            $editLink = $this->generateUrl('files_edit', array('id' => $file->getId()));
            $this->get('session')->getFlashBag()->add('success', "<a href='$editLink'>New file was created successfully.</a>");
            $nextAction = $request->get('submit') == 'save' ? 'files' : 'files_new';
            return $this->redirectToRoute($nextAction);
        }
        return $this->render('files/new.html.twig', array(
            'file' => $file,
            'form' => $form->createView(),
        ));

    }

    /**
     * Finds and displays a Files entity.
     *
     * @Route("/{id}", name="files_show")
     * @Method("GET")
     */
    public function showAction(Files $file)
    {
        $deleteForm = $this->createDeleteForm($file);
        return $this->render('files/show.html.twig', array(
            'file' => $file,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Files entity.
     *
     * @Route("/{id}/edit", name="files_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Files $file)
    {
        $deleteForm = $this->createDeleteForm($file);
        $editForm = $this->createForm('AppBundle\Form\FilesType', $file);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($file);
            $em->flush();

            $this->get('session')->getFlashBag()->add('success', 'Edited Successfully!');
            return $this->redirectToRoute('files_edit', array('id' => $file->getId()));
        }
        return $this->render('files/edit.html.twig', array(
            'file' => $file,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Files entity.
     *
     * @Route("/{id}", name="files_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Files $file)
    {

        $form = $this->createDeleteForm($file);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($file);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Files was deleted successfully');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Files');
        }

        return $this->redirectToRoute('files');
    }

    /**
     * Creates a form to delete a Files entity.
     *
     * @param Files $file The Files entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Files $file)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('files_delete', array('id' => $file->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }

    /**
     * Delete Files by id
     *
     * @Route("/delete/{id}", name="files_by_id_delete")
     * @Method("GET")
     */
    public function deleteByIdAction(Files $file)
    {
        $em = $this->getDoctrine()->getManager();

        try {
            $em->remove($file);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'The Files was deleted successfully');
        } catch (Exception $ex) {
            $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the Files');
        }

        return $this->redirect($this->generateUrl('files'));

    }


    /**
     * Bulk Action
     * @Route("/bulk-action/", name="files_bulk_action")
     * @Method("POST")
     */
    public function bulkAction(Request $request)
    {
        $ids = $request->get("ids", array());
        $action = $request->get("bulk_action", "delete");

        if ($action == "delete") {
            try {
                $em = $this->getDoctrine()->getManager();
                $repository = $em->getRepository('AppBundle:Files');

                foreach ($ids as $id) {
                    $file = $repository->find($id);
                    $em->remove($file);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add('success', 'files was deleted successfully!');

            } catch (Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', 'Problem with deletion of the files ');
            }
        }

        return $this->redirect($this->generateUrl('files'));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
    /**
     * Download Files by id
     *
     * @Route(name="file_download")
     * @Method("POST")
     */
    public function downloadAction(Request $request)
    {
        $id = $request->query->get("id");
        $em = $this->getDoctrine("Files")->getManager();
        $entity = $em->getRepository('AppBundle:Files')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find File entity.');
        }
        if( $this->container->get( 'security.authorization_checker' )->isGranted( 'IS_AUTHENTICATED_FULLY' ) )
        {
            $user = $this->container->get('security.token_storage')->getToken()->getUser();
            $username = $user->getUsername();
            $userId = $this->getUser()->getId();
        }

        $historyItem = new History();

        $historyItem->setUserID($userId);
        $historyItem->setFileID($id);
        $historyItem->setDownloadDate(new \DateTime('now'));
        $em->persist($historyItem);
        $em->flush();
        $time = date('H:i:s \O\n d/m/Y');
        $pdf =  new \setasign\FpdiProtection\FpdiProtection();
        $pdfPath = $this->getParameter('brochures_directory').'/'.$entity->getBrochure();
        $waterm_text = $username . $time;
        try {
            $page_count = $pdf->setSourceFile($pdfPath);
        } catch (Exception $pdfp_e) {
            Warning::set(
                'File is corrupted. Error: ' . $pdfp_e
            );
            return false;
        }
        for ($i = 1; $i <= $page_count; $i++) {
            $page_templ = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($page_templ);
            $page_orient = $size['orientation'];
            $page_width = (int)$size['width'];
            $page_height = (int)$size['height'];
            $pdf->addPage($page_orient);
            $pdf->SetFont('Times', 'I', 20);
            $pdf->SetTextColor(254, 146, 0);
            $text_x_pos = ceil((5 / 100) * $page_width); // Watermark position: % of page width, % of page height
            $text_y_pos = ceil((1 / 150) * $page_height);
            $pdf->SetXY($text_x_pos, $text_y_pos);
            $pdf->Write(4, $waterm_text);
            $pdf->useTemplate($page_templ);
        }



        $pdf->Output("D",$entity->getBrochure());
        return;
    }
}
