<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class NewsController extends Controller
{
	public function indexAction($year = 0, $month = 0)
	{
		$criteria = 'publish=1';

		if (0 == $year) {
			$item = $this->get('container')->getItem('news_news', '1=1');

			$date = new \DateTime($item['date'].' 00:00:00');
			$year = $date->format('Y');
			$criteria .= ' AND (YEAR(date) = '.$year.' OR YEAR(date) = '.($year-1).')';
		} elseif ($year > 0) {
			$criteria .= ' AND YEAR(date) = '.$year;
		}

		if ($month > 0) {
			$criteria .= ' AND MONTH(date) = '.$month;
		}



		$news = $this->get('container')->getItems('news_news', $criteria);

		return $this->render('news/index.html.twig', compact('news', 'year', 'month'));
	}

	public function detailAction($id)
	{
		$news = $this->get('container')->getItem('news_news', $id);
		if (!$news) {
			throw $this->createNotFoundException('Событие '.$id.'  не найдено.');
		}

		$prev = $this->get('container')->getItem('news_news', 'date<"'.$news['date'].'"');
		$next = $this->get('container')->getItem('news_news', 'date>"'.$news['date'].'"', 'date ASC');

		return $this->render('news/detail.html.twig', compact('news', 'prev', 'next'));
	}

	public function calendarAction($year = 0, $month = 0)
	{
		$sql = 'SELECT min(YEAR(created)) as min, max(YEAR(created)) as max FROM news_news';
		$years = $this->get('connection')->fetchAssoc($sql);

		return $this->render('news/calendar.html.twig', compact('years', 'year', 'month'));
	}


	public function winAction()
	{
		$ages = $this->get('container')->getItems('gallery_age', 'publish=1');
		foreach ($ages as &$age) {
			$age['pictures'] = $this->get('container')->getItems('gallery_picture', 'is_archive=0 AND publish=1 AND nomination<>"" AND age_id='.$age['id'], 'sort');
		}
		unset($age);

		$content = $this->render('news/win.html.twig', compact('ages'));


		return $content;
	}

	public function inviteAction()
	{
		$response = new Response();
		$response->setContent($this->render('news/invite.html.twig'));

		return $response;
	}
}