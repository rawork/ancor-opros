<?php

namespace Fuga\CommonBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

class ExceptionController extends Controller
{
	
	public function indexAction($status_code, $status_text)
	{
		$project_logo = PRJ_LOGO;
		$mainpage_link  = PRJ_REF.'/';

		$response = new Response();
		$response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
		$response->setCharset('UTF-8');
		$response->setContent($this->render('page.error.html.twig', compact('status_code', 'status_text', 'project_logo', 'mainpage_link')));

		return $response;
	}
	
}
