<?php

namespace Ulff\PhotoWorldBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ulff\PhotoWorldBundle\Entity\Album;
use Ulff\PhotoWorldBundle\Form\AlbumType;
use Ulff\PhotoWorldBundle\Form\MultiUploadType;
use Ulff\PhotoWorldBundle\Entity\Photo;
use Ulff\PhotoWorldBundle\Validator\Annotation\RequiresAuthorization;

class AlbumController extends Controller {

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        $album = $this->getAlbum($id);
        
        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
           array('albumid' => $id)
        );

        $zipPath = null;
        if(file_exists($album->getAlbumDirectoryPath().'/all.zip')) {
            $zipPath = $album->getAlbumDirectoryWebPath().'/all.zip';
        }

        return $this->render('UlffPhotoWorldBundle:Album:list.html.twig', array(
            'photoList' => $photoList,
            'album' => $album,
            'zippath' => $zipPath,
            'abs_www_path' => $this->container->getParameter('ulff.abs_www_path')
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Ulff\PhotoWorldBundle\Exceptions\UnauthorizedException
     */
    public function createAction() {
        $album = new Album();
        $request = $this->getRequest();
        $form = $this->createForm(new AlbumType(), $album);
        
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $securityContext = $this->container->get('security.context');
                $loggedUser = $securityContext->getToken()->getUser();

                $album->setCreatedby($loggedUser->getId());

                $em = $this->getDoctrine()->getManager();
                $em->persist($album);
                $em->flush();

                mkdir($album->getAlbumDirectoryPath());

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Album has been created'
                );

                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                    'id' => $album->getId()
                )));
            }
        }

        return $this->render('UlffPhotoWorldBundle:Album:create.html.twig', array(
            'album' => $album,
            'form' => $form->createView()
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @throws \Ulff\PhotoWorldBundle\Exceptions\UnauthorizedException
     */
    public function multiuploadAction($albumid) {

        $album = $this->getAlbum($albumid);

        $request = $this->getRequest();
        $form = $this->createForm(new MultiUploadType());

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $securityContext = $this->container->get('security.context');
                $loggedUser = $securityContext->getToken()->getUser();

                $formData = $form->getData();

                $photoFiles = $formData['files'];

                foreach($photoFiles as $photoFile) {

                    $photo = new Photo();

                    $photo->setCreatedby($loggedUser->getId());

                    $photo->setAlbum($album);
                    $photo->setPhotofile($photoFile);
                    $photo->resolveType();
                    $photo->upload();

                    $em = $this->getDoctrine()->getManager();
                    $maxSortNumber = $em->getRepository('UlffPhotoWorldBundle:Photo')->getAlbumMaxSortNumber($albumid);
                    $photo->setSortnumber(++$maxSortNumber);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($photo);
                    $em->flush();
                }

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Photos have been added'
                );

                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                    'id' => $album->getId()
                )));
            }
        }

        return $this->render('UlffPhotoWorldBundle:Album:multiupload.html.twig', array(
            'album' => $album,
            'form' => $form->createView()
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function generateAction($id) {
        $album = $this->getAlbum($id);
        
        $albumAbsolutePath = $this->getAlbumDirectoryAbsolutePath($id);
        
        $fileList = array();
        foreach(new \DirectoryIterator($albumAbsolutePath) as $fileInfo) {
            if($fileInfo->isDot()) {
                continue;
            }
            if(!$this->isFileExtensionAllowed($fileInfo->getExtension())) {
                continue;
            }
            if($this->photoExists($album, $fileInfo)) {
                continue;
            }
            $fileList[$fileInfo->getFilename()] = $fileInfo;
        }
        
        ksort($fileList);
        
        foreach(array_keys($fileList) as $fileName) {
            $this->generatePartiularPhoto($album, $fileName);
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Automatically generated album with preloaded photos'
        );
        
        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
            'id' => $album->getId()
        )));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function manageAction($id) {
        $em = $this->getDoctrine()->getManager();

        $album = $this->getAlbum($id);

        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
            array('albumid' => $id)
        );

        return $this->render('UlffPhotoWorldBundle:Album:manage.html.twig', array(
            'photoList' => $photoList,
            'album' => $album,
            'uniqid' => uniqid(),
            'abs_www_path' => $this->container->getParameter('ulff.abs_www_path')
        ));
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

        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($id);

        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
            array('albumid' => $id)
        );

        foreach($photoList as $photo) {
            @unlink($photo->getAbsolutePath());
            $em->remove($photo);
            $em->flush();
        }

        if(file_exists($album->getAlbumDirectoryPath().'/all.zip')) {
            unlink($album->getAlbumDirectoryWebPath().'/all.zip');
        }

        @rmdir($album->getAlbumDirectoryPath());
        $em->remove($album);
        $em->flush();

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Album has been removed completely'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_homepage'));
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
        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($id);

        if (!$album) {
            throw $this->createNotFoundException('Unable to find album with given id.');
        }

        $form = $this->createForm(new AlbumType(), $album);

        $request = $this->getRequest();
        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $em->persist($album);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Album changes saved'
                );

                return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
                    'id' =>  $album->getId()
                )));
            }
        }

        return $this->render('UlffPhotoWorldBundle:Album:edit.html.twig', array(
            'form' => $form->createView(),
            'album' => $album,
        ));
    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function defaultSortingAction($id) {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($id);

        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
            array('albumid' => $id)
        );

        $photoListByFileName = array();
        foreach($photoList as $photo) {
            $photoListByFileName[$photo->getPhotoFileName()] = $photo;
        }
        ksort($photoListByFileName);

        $number = 0;
        foreach($photoListByFileName as $photo) {
            $photo->setSortnumber(++$number);
            $em->persist($photo);
            $em->flush();
        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Default sorting by filename has been applied'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
            'id' =>  $id
        )));

    }

    /**
     * @RequiresAuthorization()
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createZipAction($id) {

        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($id);

        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        $photoList = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoList(
            array('albumid' => $id)
        );

        if(!empty($photoList)) {

            $zip = new \ZipArchive();
                $zip->open($album->getAlbumDirectoryPath().'/all.zip',  \ZipArchive::CREATE);
            foreach ($photoList as $photo) {
                $zip->addFile($photo->getAbsolutePath(), $photo->getPhotoFileName());
            }
            $zip->close();

        }

        $this->get('session')->getFlashBag()->add(
            'notice',
            'Created album ZIP archive'
        );

        return $this->redirect($this->generateUrl('UlffPhotoWorldBundle_managealbum', array(
            'id' =>  $id
        )));

    }
    
    protected function getAlbum($albumid) {
        $em = $this->getDoctrine()->getManager();

        $album = $em->getRepository('UlffPhotoWorldBundle:Album')->find($albumid);

        if (empty($album)) {
            throw $this->createNotFoundException('Unable to find given album.');
        }

        return $album;
    }
    
    protected function getAlbumDirectoryAbsolutePath($albumid) {
         return __DIR__ . '/../../../../web/uploads/photoworld/' . $albumid;
    }
    
    protected function isFileExtensionAllowed($extension) {
        $allowedExtensions = array(
            'jpg', 
            'jpeg', 
            'png', 
            'gif'
        );
        return in_array(strtolower($extension), $allowedExtensions);
    }
    
    protected function generatePartiularPhoto($album, $fileName) {
        $photo = new Photo();
        
        $photo->setAlbum($album);
        $photo->setCreatedate(new \DateTime());
        $photo->setCreatedby(102);
        $photo->setPath($album->getId().'/'.$fileName);

        $em = $this->getDoctrine()->getManager();
        $maxSortNumber = $em->getRepository('UlffPhotoWorldBundle:Photo')->getAlbumMaxSortNumber($album->getId());
        $photo->setSortnumber(++$maxSortNumber);

        $em = $this->getDoctrine()->getManager();
        $em->persist($photo);
        $em->flush();
    }
    
    protected function photoExists($album, $fileInfo) {
        $em = $this->getDoctrine()->getManager();
        
        $photo = $em->getRepository('UlffPhotoWorldBundle:Photo')->getPhotoByPath($album->getId().'/'.$fileInfo->getFilename());
        return !empty($photo);
    }

}
