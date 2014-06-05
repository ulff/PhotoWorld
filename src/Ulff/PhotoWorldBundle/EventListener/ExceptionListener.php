<?php

namespace Ulff\PhotoWorldBundle\EventListener;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Ulff\PhotoWorldBundle\Exceptions\UnauthorizedException;

class ExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof UnauthorizedException) {
            $response = new Response();
            $response->setStatusCode(401);
            $response->setContent('PhotoWorld: '.$exception->getMessage());
            $event->setResponse($response);
        }
    }
} 