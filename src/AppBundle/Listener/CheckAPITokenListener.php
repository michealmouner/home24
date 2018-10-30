<?php

namespace AppBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Mahmoud Mostafa <micheal.mouner@gmail.com>
 */
class CheckAPITokenListener
{
    /* @var $apiKeys array */

    private $apiKey;
    private $translator;

    /**
     * @param APIOperations $apiOperations
     */
    public function __construct($translator, $APIKey)
    {
        $this->apiKey = $APIKey;
        $this->translator = $translator;
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (strpos($request->getRequestUri(), '/api/doc') === false) {
            $apiKeyIndex = $request->headers->get('x-api-key');
            if ($apiKeyIndex != $this->apiKey) {
                $apiProblem = new \AppBundle\Api\ApiProblem(200);
                $apiProblem->set('message', $this->translator->trans('apikey.error'));
                $responseFactory = new \AppBundle\Api\ResponseFactory();
                $response = $responseFactory->createResponse($apiProblem);
                $event->setResponse($response);
                return;
            }
            $request->attributes->set('requestFrom', $apiKeyIndex);

        }
    }
}
