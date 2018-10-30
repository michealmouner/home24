<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Api\ApiProblem;
use AppBundle\Api\ResponseFactory;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;
use AppBundle\Api\ApiProblemException;
use Symfony\Component\Form\Form;

/**
 * Description of BaseController
 *
 * @author MichealMouner
 */
class BaseController extends Controller
{

    protected function getUser($id = null)
    {
        return $id ?
                $this->getDoctrine()->getRepository('AppBundle:User')->find($id) :
                parent::getUser();
    }

    protected function createSuccessfulApiResponse($data, $message = null)
    {
        $json = [
            'code'    => 200,
            'message' => $message,
            'status'  => 'success',
            'data'    => $data
        ];

        return new Response($this->serialize($json), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    protected function createErrorFormApiResponse($data, $message = null)
    {
        $json = [
            'code'    => 200,
            'message' => $message,
            'status'  => 'success',
            'errors'  => $data
        ];

        return new Response($this->serialize($json), 200, array(
            'Content-Type' => 'application/json'
        ));
    }

    protected function serialize($data, $format = 'json')
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);

        $request = $this->get('request_stack')->getCurrentRequest();

        return $this->container->get('jms_serializer')
                        ->serialize($data, $format, $context);
    }

    protected function getData($key = null, $default = null)
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        $data = json_decode($request->getContent(), true);

        if(empty($data))
        {
            $data = $request->getMethod() == "POST" ? $request->request->all() : $request->query->all();
        }

        if($key)
        {
            return (isset($data[$key]) && !empty($data[$key])) ? $data[$key] : $default;
        }
        else
        {
            return $data;
        }
    }

    /**
     *
     * @param type $message
     * @return type
     */
    public function createErrorResponse($code, $message = "")
    {
        $apiProblem = new ApiProblem($code);
        if($message)
        {
            $apiProblem->set('message', $message);
        }

        $responseFactory = new ResponseFactory();

        return $responseFactory->createResponse($apiProblem);
    }

    protected function throwApiProblemValidationException(Form $form, $code = 400)
    {
        $errors = $this->getErrorsFromForm($form);

        $apiProblem = new ApiProblem(
                $code
        );

        $apiProblem->set('errors', $errors);
        if(is_array($errors) && !empty($errors))
        {
            $firstErrorArr = array_shift($errors);
            $apiProblem->set('message', $firstErrorArr[0]);
        }

        throw new ApiProblemException($apiProblem);
    }

    protected function getErrorsFromForm(Form $form)
    {
        $errors = array();
        foreach($form->getErrors() as $error)
        {
            $errors[] = $error->getMessage();
        }
        foreach($form->all() as $childForm)
        {
            if($childForm)
            {
                if($childErrors = $this->getErrorsFromForm($childForm))
                {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    protected function processForm(\Symfony\Component\HttpFoundation\Request $request, Form $form, $clearMissing = null)
    {
        $data = json_decode($request->getContent(), true);
        if(empty($data))
        {
            $data = $request->request->all();
        }

        if($data === null)
        {
            $apiProblem = new ApiProblem(200/* 400 */, ApiProblem::TYPE_INVALID_REQUEST_BODY_FORMAT);

            throw new ApiProblemException($apiProblem);
        }

        $clearMissing = !is_null($clearMissing) ? $clearMissing : $request->getMethod() != 'PATCH';
        $form->submit($data, $clearMissing);
    }

}
