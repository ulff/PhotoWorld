<?php

namespace Ulff\PhotoWorldBundle\Validator\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ulff\UserBundle\Entity\User;

/**
 * @Annotation
 */
class RequiresAuthorization
{
    /**
     * @var string
     */
    public $schoolId;

    /**
     * @var string
     */
    public $resourceId;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Controller
     */
    private $controller;

    /**
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     */
    public function validate()
    {
        $securityContext = $this->container->get('security.context');
        $loggedUser = $securityContext->getToken()->getUser();

        if(!$loggedUser instanceof User) {
            $this->container->get('session')->getFlashBag()->add(
                'failure',
                'You have to be logged in to perform this action'
            );

            $redirectUrl = $this->container->get('router')->generate('fos_user_security_login');

            // todo: replace that with proper solution
            header("Location: ".$redirectUrl);
            exit;
        }
    }

}
