<?php

namespace Ulff\PhotoWorldBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ulff\PhotoWorldBundle\Entity\Photo;
use Ulff\PhotoWorldBundle\Form\PhotoType;
use Ulff\PhotoWorldBundle\Validator\Annotation\RequiresAuthorization;

/**
 * Description of PhotoController
 *
 * @author ulff
 */
class PhotoController extends Controller {

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);
        
        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }
        
        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
           array('albumid' => $photo->getAlbum()->getId())
        );
        
        $photoPosition = array_search($photo, $photoList);
        
        $nextPhoto = $em->getRepository('UlffPhotoWorldBundle:Photo')->getNextPhoto($photo, $photo->getAlbum()->getId());
        $previousPhoto = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPreviousPhoto($photo, $photo->getAlbum()->getId());
        
        return $this->render('UlffPhotoWorldBundle:Photo:show.html.twig', array(
            'photo' => $photo,
            'album' => $photo->getAlbum(),
            'nextid' => !empty($nextPhoto) ? $nextPhoto->getId() : null,
            'previd' => !empty($previousPhoto) ? $previousPhoto->getId() : null,
            'totalphotos' => count($photoList),
            'position' => ++$photoPosition
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $albumid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction($albumid) {
        $photo = new Photo();
        $form = $this->createForm(new PhotoType(), $photo);
        
        $album = $this->getAlbum($albumid);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $securityContext = $this->container->get('security.context');
                $loggedUser = $securityContext->getToken()->getUser();

                $photo->setCreatedby($loggedUser->getId());

                $photo->setAlbum($album);
                $photo->upload();

                $em = $this->getDoctrine()->getManager();
                $maxSortNumber = $em->getRepository('UlffPhotoWorldBundle:Photo')->getAlbumMaxSortNumber($albumid);
                $photo->setSortnumber(++$maxSortNumber);

                $em = $this->getDoctrine()->getManager();
                $em->persist($photo);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Photo has been uploaded'
                );
                
                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                    'id' => $albumid
                )));
            }
        }

        return $this->render('UlffPhotoWorldBundle:Photo:upload.html.twig', array(
            'form' => $form->createView(),
            'album' => $album
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function rotateleftAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        $this->rotatePhoto($photo, 90);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been rotated'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' =>  $photo->getAlbum()->getId()
            )).'#photo-id-'.$photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function rotaterightAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        $this->rotatePhoto($photo, 270);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been rotated'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' =>  $photo->getAlbum()->getId()
            )).'#photo-id-'.$photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        unlink($photo->getAbsolutePath());
        $em->remove($photo);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been removed'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
            'id' => $photo->getAlbum()->getId()
        )));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        $form = $this->createForm(new PhotoType(), $photo);
        $form->remove('photofile');

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $em->persist($photo);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Photo has been modified'
                );

                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                    'id' =>  $photo->getAlbum()->getId()
                    )).'#photo-id-'.$photo->getId()
                );
            }
        }

        return $this->render('UlffPhotoWorldBundle:Photo:edit.html.twig', array(
            'form' => $form->createView(),
            'photo' => $photo,
            'album' => $photo->getAlbum()
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function moveupAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        $previousPhoto = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPreviousPhoto($photo, $photo->getAlbum()->getId());
        if(!empty($previousPhoto)) {
            $swapNumber = $photo->getSortnumber();
            $photo->setSortnumber($previousPhoto->getSortnumber());
            $previousPhoto->setSortnumber($swapNumber);
            $em->persist($photo);
            $em->persist($previousPhoto);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been moved'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' =>  $photo->getAlbum()->getId()
            )).'#photo-id-'.$photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function movedownAction($id) {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->find($id);

        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        $nextPhoto = $em->getRepository('UlffPhotoWorldBundle:Photo')->getNextPhoto($photo, $photo->getAlbum()->getId());
        if(!empty($nextPhoto)) {
            $swapNumber = $photo->getSortnumber();
            $photo->setSortnumber($nextPhoto->getSortnumber());
            $nextPhoto->setSortnumber($swapNumber);
            $em->persist($photo);
            $em->persist($nextPhoto);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been moved'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' =>  $photo->getAlbum()->getId()
            )).'#photo-id-'.$photo->getId()
        );
    }

    protected function rotatePhoto($photo, $angle) {
        $imageService = $this->get('image.handling');
        $imageService->open($photo->getAbsolutePath())
            ->rotate($angle)
            ->save($photo->getAbsolutePath());
    }
    
    protected function getAlbum($albumid) {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($albumid);

        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        return $album;
    }

}
