<?php

namespace Ulff\PhotoWorldBundle\Validator\Annotation;

use Doctrine\Common\Annotations\Annotation;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Ulff\PhotoWorldBundle\Exceptions\UnauthorizedException;
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
            throw new UnauthorizedException();
        }
    }

}
