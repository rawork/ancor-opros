<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class AboutController extends Controller
{
	public function indexAction()
	{
		$tabs = $this->get('container')->getItems('about_tab', 'publish=1');

		return $this->render('about/index.html.twig', compact('tabs'));
	}

	public function sliderAction()
	{
		$slides = $this->get('container')->getItems('about_slide', 'publish=1');
		$vote_button_active = $this->getManager('Fuga:Common:Param')->getValue('about', 'vote_button_active');

		return $this->render('about/slider.html.twig', compact('slides', 'vote_button_active'));
	}

	public function juriAction()
	{
		$people = $this->get('container')->getItems('about_juri', 'publish=1');

		return $this->render('about/juri.html.twig', compact('people'));
	}

	public function memberAction($id)
	{
		$member = $this->get('container')->getItem('about_juri', 'id='.$id.' AND publish=1');

		if (!$member) {
			throw $this->createNotFoundException('Несушествующая ссылка');
		}

		return $this->render('about/member.html.twig', compact('member'));
	}
	
}