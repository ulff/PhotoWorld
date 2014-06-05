<?php

namespace Ulff\PhotoWorldBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GalleryController extends Controller {

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction() {
        $em = $this->getDoctrine()->getManager();
        $albumList = $em->getRepository('UlffPhotoWorldBundle:Album')->getAlbumList();

        return $this->render('UlffPhotoWorldBundle:Gallery:list.html.twig', array(
            'albumList' => $albumList
        ));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function aboutAction() {
        return $this->render('UlffPhotoWorldBundle:Gallery:about.html.twig');
    }

}
