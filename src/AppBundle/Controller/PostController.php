<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Entity\Post;
use Doctrine\Common\Cache\ArrayCache;
use AppBundle\Form\PostType;

class PostController extends BaseController
{

    /**
     * get all posts
     * @ApiDoc(
     *  description="return all posts for the logged in user",
     *  tags={
     *      "development"
     *  },
     *  section="Post",
     *  authentication=true,
     *  parameters={
     *      {"name"="offset", "dataType"="string", "required"=false, "format"="start from record default 0"},
     *      {"name"="limit", "dataType"="string", "required"=false, "format"="number of recorders needed default 10"},
     *      {"name"="order_by", "dataType"="string", "required"=false, "format"="orderby default id DESC"},
     *      {"name"="filters", "dataType"="array", "required"=false, "format"="array of filters"}
     *  },
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not Found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/posts")
     * @Method("GET")
     */
    public function postsAction(Request $request)
    {

        /* @var $apcCache  \Doctrine\Common\Cache\FilesystemCache */
        $apcCache = $this->container->get('vendor_filesystem_result_cache');
//        echo get_class($apcCache);exit;
        try
        {
            $offset = $this->getData('offset', 0);
            $limit = $this->getData('limit', 10);
            $order_by = $this->getData('order_by', ['id' => 'DESC']);

            $filters = !is_null($this->getData('filters')) ? $this->getData('filters') : array();

            $cacheKey = md5($offset . "-" . $limit . "-" . json_encode($order_by) . "-" . json_encode($filters));
            if($apcCache->contains($cacheKey))
            {
                return $this->createSuccessfulApiResponse(['cache' => true, 'posts' => $apcCache->fetch($cacheKey)]);
            }
            $em = $this->getDoctrine()->getManager();
            $posts = $em->getRepository('AppBundle:Post')->findBy($filters, $order_by, $limit, $offset);
            $apcCache->save($cacheKey, $posts, $this->getParameter('cache_posts_in_seconds'));

            return $this->createSuccessfulApiResponse(['cache' => false, 'posts' => $posts]);
        } catch (\Exception $e)
        {
            return $this->createErrorResponse(500);
        }
    }

    /**
     * get one post
     * @ApiDoc(
     *  description="return one post for the logged in user",
     *  tags={
     *      "development"
     *  },
     *  section="Post",
     *  authentication=true,
     *  parameters={
     *      {"name"="offset", "dataType"="string", "required"=false, "format"="start from record default 0"},
     *      {"name"="limit", "dataType"="string", "required"=false, "format"="number of recorders needed default 10"},
     *      {"name"="order_by", "dataType"="string", "required"=false, "format"="orderby default id DESC"},
     *      {"name"="filters", "dataType"="array", "required"=false, "format"="array of filters"}
     *  },
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not Found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/posts/{id}")
     * @Method("GET")
     */
    public function postAction(Request $request)
    {

        $post = $this->getEntity($request->get('id'));

        if(!$post)
        {
            return $this->createErrorResponse(404, "Post not found.");
        }
        return $this->createSuccessfulApiResponse($post);
    }

    /**
     * get one post
     * @ApiDoc(
     *  description="return one post for the logged in user",
     *  tags={
     *      "development"
     *  },
     *  section="Post",
     *  authentication=true,
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=false}
     *  },
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not Found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/posts/{id}/delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request)
    {

        $post = $this->getEntity($request->get('id'));

        if(!$post)
        {
            return $this->createErrorResponse(404, "Post not found.");
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($post);
        $em->flush();

        return $this->createSuccessfulApiResponse([]);
    }

    /**
     * get one post
     * @ApiDoc(
     *  description="return one post for the logged in user",
     *  tags={
     *      "development"
     *  },
     *  section="Post",
     *  authentication=true,
     *  parameters={
     *      {"name"="title", "dataType"="integer", "required"=true},
     *      {"name"="details", "dataType"="integer", "required"=true},
     *      {"name"="isPublished", "dataType"="boolean", "required"=true}
     *  },
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not Found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/posts/add")
     * @Method("POST")
     */
    public function addAction(Request $request)
    {

        $post = new Post();

        $form = $this->createForm(PostType::class, $post, array("method" => $request->getMethod()));
        $this->processForm($request, $form);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();

            return $this->createSuccessfulApiResponse($post);
        }
        $this->throwApiProblemValidationException($form, 400);
    }

    /**
     * get one post
     * @ApiDoc(
     *  description="return one post for the logged in user",
     *  tags={
     *      "development"
     *  },
     *  section="Post",
     *  authentication=true,
     *  parameters={
     *      {"name"="title", "dataType"="integer", "required"=true},
     *      {"name"="details", "dataType"="integer", "required"=true},
     *      {"name"="isPublished", "dataType"="boolean", "required"=true}
     *  },
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not Found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/posts/{id}/update")
     * @Method("POST")
     */
    public function updateAction(Request $request)
    {

        $em = $this->getDoctrine()->getManager();
        $post = $this->getEntity($request->get("id"));
        if(!$post)
        {
            return $this->createErrorResponse(404, "Post not found");
        }
        $form = $this->createForm(PostType::class, $post, array("method" => $request->getMethod()));
        $this->processForm($request, $form);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($post);
            $em->flush();

            return $this->createSuccessfulApiResponse($post);
        }
        $this->throwApiProblemValidationException($form, 400);
    }

    public function putAction(Request $request)
    {
        try
        {
            $em = $this->getDoctrine()->getManager();
            $request->setMethod('PATCH'); //Treat all PUTs as PATCH
            $form = $this->createForm(get_class(new PostType()), $entity, array("method" => $request->getMethod()));
            $this->removeExtraFields($request, $form);
            $form->handleRequest($request);
            if($form->isValid())
            {
                $em->flush();

                return $entity;
            }

            return FOSView::create(array('errors' => $form->getErrors()), Codes::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e)
        {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function patchAction(Request $request)
    {
        return $this->putAction($request, $entity);
    }

    public function deletehhhAction(Request $request)
    {
        try
        {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();

            return null;
        } catch (\Exception $e)
        {
            return FOSView::create($e->getMessage(), Codes::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getEntity($id)
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('AppBundle:Post')->find($id);
    }

}
