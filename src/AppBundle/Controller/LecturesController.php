<?php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use AppBundle\Entity\Files;

class LecturesController extends FOSRestController
{

      /**
     * @Rest\Get("/api/lectures")
     */
    public function getAction() {
        $lectures = $this->getDoctrine()
        ->getRepository('AppBundle:Files')
        ->findAll();

        $data = array('lectures' => array());
        foreach ($lectures as $lecture) {
            $data['lectures'][] = $this->serializeLecture($lecture);
        }
        $response = new Response(json_encode($data), 200);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
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