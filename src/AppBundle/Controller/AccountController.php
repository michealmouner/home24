<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class AccountController extends BaseController
{

    /**
     * Login with existing user
     * @ApiDoc(
     *  description="Login user with basic http auth",
     *  tags={
     *      "development"="Chocolate"
     *  },
     *  section="User",
     *  parameters={
     *      {"name"="email", "dataType"="string", "required"=true, "format"="{email address}"},
     *      {"name"="password", "dataType"="string", "required"=true}
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
     * @Route("/api/login")
     * @Method("POST")
     */
    public function loginAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        /* @var $user \AppBundle\Entity\User */
        $user = $em->getRepository("AppBundle:User")->findOneBy(['email' => $request->get('email')]);

        if(!$user)
        {
            return $this->createErrorResponse(404, "Erorr email or password.");
        }

        $isValid = $this->get("security.password_encoder")->isPasswordValid(
                $user, $request->get('password'));

        if(!$isValid)
        {
            return $this->createErrorResponse(404, "Erorr email or password.");
        }

        $token = $this->get('lexik_jwt_authentication.encoder')
                ->encode([
            'id'    => $user->getId(),
            'email' => $user->getUserName(),
            'hash'  => $user->getHash(),
        ]);

        return $this->createSuccessfulApiResponse([
                    'token' => $token,
                    'email' => $user->getEmail(),
                    'id'    => $user->getId(),
        ]);
    }

    /**
     * get User profile
     * @ApiDoc(
     *  description="get user profile data",
     *  tags={
     *      "development"
     *  },
     *  section="User",
     *  authentication=true,
     *  statusCodes={
     *      200="Returned on success",
     *      401="Unauthorized",
     *      404="Not found"
     *  }
     * )
     * @author Micheal Mounir <micheal.mouner@gmail.com>
     * @param Request $request
     * @return JsonResponse
     *
     * @Route("/api/profile")
     * @Method("GET")
     */
    public function profileAction(Request $request)
    {
        $user = $this->getUser();
        return $this->createSuccessfulApiResponse($user);
    }

}
