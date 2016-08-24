<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;

class HistoryController extends Controller
{
	public function indexAction()
	{
		$competitions = $this->get('container')->getItems('history_competition', 'publish=1');
		$firstCompetition = reset($competitions);
		foreach ($competitions as &$competition) {
			$competition['gallery'] = $this->get('container')->getItems('history_gallery', 'publish=1 AND competition_id='.$competition['id']);
			$competition["video"] = $this->get('container')->getItem('history_video', 'publish=1 AND competition_id='.$competition['id']);
			$competition['news'] = $this->get('container')->getItems('news_news', 'publish=1 AND competition_id='.$competition['id'], 'date DESC', 2);
		}
		unset($competition);

		return $this->render('history/index.html.twig', compact('competitions', 'firstCompetition'));
	}

	public function detailAction($id)
	{
		$competition = $this->get('container')->getItem('history_competition', $id);

		if (!$competition) {
			throw $this->createNotFoundException('Несуществующая страница');
		}

		$gallery = $this->get('container')->getItems('history_gallery', 'publish=1 AND competition_id='.$id);
		$videos = $this->get('container')->getItems('history_video', 'publish=1 AND competition_id='.$id);
		$news = $this->get('container')->getItems('news_news', 'publish=1 AND competition_id='.$id);

		return $this->render('history/detail.html.twig', compact('competition', 'gallery', 'news', 'videos'));
	}


	public function visualAction($id)
	{
		$competition = $this->get('container')->getItem('history_competition', $id);

		return $this->render('history/visual.html.twig', compact('competition'));
	}
}