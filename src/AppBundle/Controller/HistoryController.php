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
use AppBundle\Entity\User;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer;
/**
 * History controller.
 *
 * @Route("/download_history")
 */
class HistoryController extends Controller
{

      /**
     * Lists all Files entities.
     *
     * @Route("/", name="download_history")
     * @Method("GET")
     */
    public function getAction() {
        $files_array = $this->getDoctrine()
        ->getRepository('AppBundle:Files')
        ->findAll();
        $history_array = $this->getDoctrine()
        ->getRepository('AppBundle:History')
        ->findAll();

        $dataHistory = array();
        foreach ($history_array as $history) {
            $dataHistory[] = $this->serializeHistory($history);
        }
        $dataFiles = array();
        foreach ($files_array as $files) {
            $dataFiles[] = $this->serializeLecture($files);
        }

        // $file_ids_array = array();
        // foreach($dataHistory as $history) {
        //     array_push($file_ids_array, $history['file_id']);
        //     };

        // $files_ids = join(",",$file_ids_array); 

        // $em = $this->getDoctrine()->getManager();
        // $queryBuilder = $em->createQueryBuilder();
        // $queryBuilder->select('*')
        // ->from('Files')
        // ->leftJoin('fos_user', 'f', 'WITH', 'Files.user_id = Users.user_id"')
        // ->

        return $this->render('history/index.html.twig', array(
            'dataFiles' => $dataFiles,
            'dataHistory' => $dataHistory,
       

        ));

    }

    private function serializeHistory(History $history)
    {
        return array(
            'download_date' => $history->getDownloadDate(),
            'user_id' => $history->getUserID(),
            'file_id' => $history->getFileID(),
        );
    }

    private function serializeLecture(Files $lecture)
    {
        return array(
            'file_name' => $lecture->getFileName(),
            'subject' => $lecture->getSubject(),
            'brochure' => $lecture->getBrochure(),
        );
    }

}