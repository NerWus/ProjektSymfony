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
 * Files controller.
 *
 * @Route("/statistics")
 */
class StatisticController extends Controller
{

      /**
     * Lists all Files entities.
     *
     * @Route("/", name="statistics")
     * @Method("GET")
     */
    public function getAction() {
        $user_array = $this->getDoctrine()
        ->getRepository('AppBundle:User')
        ->findAll();
        $history_array = $this->getDoctrine()
        ->getRepository('AppBundle:History')
        ->findAll();

        $data = array();
        foreach ($history_array as $history) {
            $data[] = $this->serializeHistory($history);
        }

        $user_count = count($user_array);
        $ile_dni=0;
        $ile_tydz=0;
        $ile_mies=0;
        $ile_sem=0;
        $month=date('m');
        $today = date("Y.m.d");
        $akt_tydz=date('W');
        
        $serializer = new Serializer(array(new DateTimeNormalizer()));

        foreach($data as $history):
            if(date("Y.m.d",strtotime(($serializer->normalize($history['download_date']))))==$today)
                $ile_dni++;
            
            $mies=explode("-",$serializer->normalize($history['download_date']));
            if($mies[1]==$month)
                $ile_mies++;
            
            if($mies[1]>=10 and $mies[1]<=12 or $mies[1]==1 )
                $ile_sem++;
             
            $plik_tydz=date("W",strtotime($serializer->normalize($history['download_date'])));
            if($plik_tydz==$akt_tydz)
                $ile_tydz++;
            
        endforeach;

        return $this->render('statistics/index.html.twig', array(
            'user_count' => $user_count,
            'day_number' => $ile_dni,
            'week_number' => $ile_tydz,
            'month_number' => $ile_mies,

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

}