<?php

namespace Fuga\PublicBundle\Controller;

use Fuga\CommonBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

class PollController extends Controller
{
	public function indexAction()
	{
		return $this->render('poll/index.html.twig');
	}

	public function dataAction()
	{
		$respondent = $this->get('request')->request->get('respondent');
		$questions = $this->get('container')->getItems('poll_question', 'publish=1');
		$result = $this->get('container')->getItem('poll_results', 'code="'.$respondent.'"');
		$result['polldata'] = $result['polldata'] ? json_decode($result['polldata'], true) : array();
		$json = array('questions' => array(), 'result' => $result);
		foreach ($questions as $question) {
			$question['answers'] = array_values($this->get('container')->getItems('poll_answer', 'publish=1 AND question_id='.$question['id']));

			$json['questions'][$question['code'].$question['branch']] = $question;
		}

		$response = new JsonResponse();
		$response->setData($json);

		return $response;
	}

	public function respondentAction()
	{
		$guid = uniqid();
		$this->get('container')->addItem(
			'poll_results',
			array(
				'code' => $guid,
				'branch' => '',
				'polldata' => '',
				'is_end' => 0,
			)
		);

		$response = new JsonResponse();
		$response->setData(array(
			'respondent' => $guid
		));

		return $response;
	}

	public function saveAction()
	{
		$respondent = $this->get('request')->request->get('respondent');
		$data = $this->get('request')->request->get('data');
		$isEnd = 0;
		if($data['question'] == '9') {
			$isEnd = 1;
		}
		$this->get('container')->updateItem(
			'poll_results',
			array(
				'branch' => $data['branch'],
				'question' => $data['question'],
				'answer1' => isset($data['answer1']) ? $data['answer1'] : '',
				'answer2' => isset($data['answer2']) ? $data['answer2'] : '',
				'polldata' => isset($data['polldata']) ? json_encode($data['polldata']) : '',
				'is_end' => $isEnd,
			),
			array('code' => $respondent)
		);

		$response = new JsonResponse();
		$response->setData(array(
			'status' => true,
		));

		return $response;
	}
}