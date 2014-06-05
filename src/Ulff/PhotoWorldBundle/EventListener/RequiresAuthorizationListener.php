<?php

namespace Ulff\PhotoWorldBundle\EventListener;

use Doctrine\Common\Annotations\Reader;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Ulff\PhotoWorldBundle\Validator\Annotation\RequiresAuthorization;

class RequiresAuthorizationListener
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     * @param Container $serviceContainer
     */
    public function __construct(Reader $reader, Container $serviceContainer)
    {
        $this->reader = $reader;
        $this->serviceContainer = $serviceContainer;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onKernelController(FilterControllerEvent $event)
    {
        if (!is_array($controller = $event->getController())) {
            return;
        }

        $reflectionClass = new \ReflectionObject($controller[0]);
        $reflectionMethod = $reflectionClass->getMethod($controller[1]);
        $allAnnotations = $this->reader->getMethodAnnotations($reflectionMethod);

        foreach ($allAnnotations as $annotation) {
            $this->requiresAuthorization($event, $annotation);
        }
    }

    /**
     * @param FilterControllerEvent $event
     * @param object $annotation
     */
    private function requiresAuthorization(FilterControllerEvent $event, $annotation)
    {
        if ($annotation instanceof RequiresAuthorization) {
            $annotation->setContainer($this->serviceContainer);
            $annotation->validate();
        }
    }
} 