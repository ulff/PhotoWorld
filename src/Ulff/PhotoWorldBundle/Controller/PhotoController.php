<?php

namespace Ulff\PhotoWorldBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ulff\PhotoWorldBundle\Entity\Photo;
use Ulff\PhotoWorldBundle\Entity\Like;
use Ulff\PhotoWorldBundle\Form\PhotoType;
use Ulff\PhotoWorldBundle\Validator\Annotation\RequiresAuthorization;

/**
 * Description of PhotoController
 *
 * @author ulff
 */
class PhotoController extends Controller
{

    protected $entityManager;

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $photoList = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
            array('albumid' => $photo->getAlbum()->getId())
        );

        $photoPosition = array_search($photo, $photoList);

        $nextPhoto = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getNextPhoto($photo, $photo->getAlbum()->getId());
        $previousPhoto = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getPreviousPhoto($photo, $photo->getAlbum()->getId());

        $likesList = $this->entityManager->getRepository('UlffPhotoWorldBundle:Like')->listLikes(array(
            'photoid' => $photo->getId()
        ));

        $securityContext = $this->container->get('security.context');
        $loggedUser = $securityContext->getToken()->getUser();
        $existingLike = $this->entityManager->getRepository('UlffPhotoWorldBundle:Like')->getLike(array(
            'photoid' => $photo->getId(),
            'userid' => $loggedUser->getId()
        ));

        return $this->render('UlffPhotoWorldBundle:Photo:show.html.twig', array(
            'photo' => $photo,
            'album' => $photo->getAlbum(),
            'nextid' => !empty($nextPhoto) ? $nextPhoto->getId() : null,
            'previd' => !empty($previousPhoto) ? $previousPhoto->getId() : null,
            'totalphotos' => count($photoList),
            'position' => ++$photoPosition,
            'abs_www_path' => $this->container->getParameter('ulff.abs_www_path'),
            'likes' => $likesList,
            'ilikeit' => !empty($existingLike)
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $albumid
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction($albumid)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
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
                $photo->resolveType();
                $photo->upload();

                $maxSortNumber = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getAlbumMaxSortNumber($albumid);
                $photo->setSortnumber(++$maxSortNumber);

                $this->entityManager->persist($photo);
                $this->entityManager->flush();

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
    public function rotateleftAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $this->rotatePhoto($photo, 90);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been rotated'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' => $photo->getAlbum()->getId()
            )) . '#photo-id-' . $photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function rotaterightAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $this->rotatePhoto($photo, 270);

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been rotated'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' => $photo->getAlbum()->getId()
            )) . '#photo-id-' . $photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function removeAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        unlink($photo->getAbsolutePath());
        $this->entityManager->remove($photo);
        $this->entityManager->flush();

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
    public function editAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $form = $this->createForm(new PhotoType(), $photo);
        $form->remove('photofile');

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $this->entityManager->persist($photo);
                $this->entityManager->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Photo has been modified'
                );

                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                        'id' => $photo->getAlbum()->getId()
                    )) . '#photo-id-' . $photo->getId()
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
    public function moveupAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $previousPhoto = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getPreviousPhoto($photo, $photo->getAlbum()->getId());
        if (!empty($previousPhoto)) {
            $swapNumber = $photo->getSortnumber();
            $photo->setSortnumber($previousPhoto->getSortnumber());
            $previousPhoto->setSortnumber($swapNumber);
            $this->entityManager->persist($photo);
            $this->entityManager->persist($previousPhoto);
            $this->entityManager->flush();
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been moved'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' => $photo->getAlbum()->getId()
            )) . '#photo-id-' . $photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function movedownAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $nextPhoto = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->getNextPhoto($photo, $photo->getAlbum()->getId());
        if (!empty($nextPhoto)) {
            $swapNumber = $photo->getSortnumber();
            $photo->setSortnumber($nextPhoto->getSortnumber());
            $nextPhoto->setSortnumber($swapNumber);
            $this->entityManager->persist($photo);
            $this->entityManager->persist($nextPhoto);
            $this->entityManager->flush();
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Photo has been moved'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                'id' => $photo->getAlbum()->getId()
            )) . '#photo-id-' . $photo->getId()
        );
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addLikeAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $securityContext = $this->container->get('security.context');
        $loggedUser = $securityContext->getToken()->getUser();

        $existingLike = $this->entityManager->getRepository('UlffPhotoWorldBundle:Like')->getLike(array(
            'userid' => $loggedUser->getId(),
            'photoid' => $photo->getId()
        ));

        if(!empty($existingLike)) {
            $this->get('session')->getFlashBag()->add(
                'failure',
                'You have already added a like for this photo'
            );

            return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_showphoto', array(
                'id' => $photo->getId()
            )));
        }

        $like = new Like();
        $like->setUser($loggedUser);
        $like->setPhoto($photo);
        $this->entityManager->persist($like);
        $this->entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Your like has beed added'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_showphoto', array(
            'id' => $photo->getId()
        )));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function unlikeAction($id)
    {
        $this->entityManager = $this->getDoctrine()->getManager();
        $photo = $this->getPhoto($id);

        $securityContext = $this->container->get('security.context');
        $loggedUser = $securityContext->getToken()->getUser();

        $existingLike = $this->entityManager->getRepository('UlffPhotoWorldBundle:Like')->getLike(array(
            'userid' => $loggedUser->getId(),
            'photoid' => $photo->getId()
        ));

        $this->entityManager->remove($existingLike);
        $this->entityManager->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Your like has beed removed'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_showphoto', array(
            'id' => $photo->getId()
        )));
    }

    protected function rotatePhoto($photo, $angle)
    {
        $imageService = $this->get('image.handling');
        $imageService->open($photo->getAbsolutePath())
            ->rotate($angle)
            ->save($photo->getAbsolutePath());
    }

    protected function getAlbum($albumid)
    {
        $album = $this->entityManager->getRepository('UlffPhotoWorldBundle:Album')->find($albumid);
        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        return $album;
    }

    protected function getPhoto($photoid)
    {
        $photo = $this->entityManager->getRepository('UlffPhotoWorldBundle:Photo')->find($photoid);
        if (!$photo) {
            throw $this->createNotFoundException('Unable to find photo with given id.');
        }

        return $photo;
    }

}
