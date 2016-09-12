<?php

namespace Fuga\PublicBundle\Controller;

use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Fuga\AdminBundle\Controller\AdminController;

use PHPExcel;
use PHPExcel_Writer_Excel2007;
use PHPExcel_Style_Alignment;

class PolladminController extends AdminController
{
	public function indexAction()
	{
		$state = 'content';
		$module = 'poll';

		$response = new Response();
		$response->setContent($this->render('poll/admin.export.html.twig', compact('state', 'module')));
		$response->prepare($this->get('request'));

		return $response;
	}


	public function reportaAction() {
		$fieldIndexes = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G',
			'H', 'I', 'J', 'K', 'L', 'M', 'N',
			'O', 'P', 'Q', 'R', 'S', 'T', 'U',
			'V', 'W', 'X', 'Y', 'Z',
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG',
			'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN',
			'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU',
			'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG',
			'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN',
			'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU',
			'BV', 'BW', 'BX', 'BY', 'BZ',
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG',
			'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN',
			'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU',
			'CV', 'CW', 'CX', 'CY', 'CZ',
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG',
			'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN',
			'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU',
			'DV', 'DW', 'DX', 'DY', 'DZ',
		);
		$filename = join('_', array('opros_group_a', date('Y_m_d_H_i_s'))).'.xlsx';
		$filepath = PRJ_DIR . '/upload/'.$filename;

		$questions = $this->get('container')->getItems('poll_question', 'publish=1 AND is_last=0 AND code>2 AND (branch="A" OR branch="")');
		$answers = array();
		foreach($questions as $question){
			$answers = array_merge($answers, $this->get('container')->getItems('poll_answer', 'question_id='.$question['id']));
		}
		$respondents = $this->get('container')->getItems('poll_results', 'branch="A"');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Ancor opros site");
		$objPHPExcel->getProperties()->setLastModifiedBy("Ancor opros site");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setDescription("Opros data report Group A");


		// Add some data
		$objPHPExcel->setActiveSheetIndex(0);

		$style = array(
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			)
		);

		$objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($style);

		$firstRow = array('Респондент','','Город','Год');

		foreach($answers as $answer){
			$firstRow[] = join(' ', array($answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch'], $answer['name']));
		}

		// phpexcel add first row

		foreach ($firstRow as $key => $titleCell) {
			$cellCode = $fieldIndexes[$key];
			// set column width
			$objPHPExcel->getActiveSheet()->getColumnDimension($cellCode)->setWidth($key < 3 ? "13" : "30");

			// set text wrap
			$objPHPExcel->getActiveSheet()->getStyle($cellCode.'1:'.$cellCode.$objPHPExcel->getActiveSheet()->getHighestRow())
				->getAlignment()->setWrapText(true);

			$objPHPExcel->getActiveSheet()->SetCellValue($cellCode.'1', $titleCell);
		}

		$excelRowNum = 2;



		foreach ($respondents as $respondent) {
			$answersData = json_decode($respondent['polldata'], true);
			if (is_null($answersData)){
				continue;
			}
			$answersData = array_combine(array_column($answersData,'code'), $answersData);
			$fields = array($respondent['code'], $respondent['branch'], $respondent['answer1'],$respondent['answer2']);

			foreach($answers as $answer){
				$answerData = '';
				if(array_key_exists($answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch'], $answersData)) {
					if (in_array($answer['id'], $answersData[$answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch']]['answers'])) {
						$answerData = 1;
					}
				}
				$fields[] = $answerData;
			}

			// phpexcel add data row
			foreach ($fields as $key => $dataCell) {
				$objPHPExcel->getActiveSheet()->SetCellValue($fieldIndexes[$key].$excelRowNum, $dataCell);
			}

			$excelRowNum++;
		}

		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Результаты опроса Группа A');


		// Save Excel 2007 file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($filepath);

		if (!$this->get('fs')->exists($filepath)) {
			throw $this->createNotFoundException('File not found');
		}

		$response = new BinaryFileResponse($filepath);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$filename
		);
		$response->prepare($this->get('request'));

		return $response;
	}

	public function reportbAction() {
		$fieldIndexes = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G',
			'H', 'I', 'J', 'K', 'L', 'M', 'N',
			'O', 'P', 'Q', 'R', 'S', 'T', 'U',
			'V', 'W', 'X', 'Y', 'Z',
			'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG',
			'AH', 'AI', 'AJ', 'AK', 'AL', 'AM', 'AN',
			'AO', 'AP', 'AQ', 'AR', 'AS', 'AT', 'AU',
			'AV', 'AW', 'AX', 'AY', 'AZ',
			'BA', 'BB', 'BC', 'BD', 'BE', 'BF', 'BG',
			'BH', 'BI', 'BJ', 'BK', 'BL', 'BM', 'BN',
			'BO', 'BP', 'BQ', 'BR', 'BS', 'BT', 'BU',
			'BV', 'BW', 'BX', 'BY', 'BZ',
			'CA', 'CB', 'CC', 'CD', 'CE', 'CF', 'CG',
			'CH', 'CI', 'CJ', 'CK', 'CL', 'CM', 'CN',
			'CO', 'CP', 'CQ', 'CR', 'CS', 'CT', 'CU',
			'CV', 'CW', 'CX', 'CY', 'CZ',
			'DA', 'DB', 'DC', 'DD', 'DE', 'DF', 'DG',
			'DH', 'DI', 'DJ', 'DK', 'DL', 'DM', 'DN',
			'DO', 'DP', 'DQ', 'DR', 'DS', 'DT', 'DU',
			'DV', 'DW', 'DX', 'DY', 'DZ',
		);
		$filename = join('_', array('opros_group_b', date('Y_m_d_H_i_s'))).'.xlsx';
		$filepath = PRJ_DIR . '/upload/'.$filename;

		$questions = $this->get('container')->getItems('poll_question', 'publish=1 AND is_last=0 AND code>2 AND (branch="B" OR branch="" OR id=7)');
		$answers = array();
		foreach($questions as $question){
			$answers = array_merge($answers, $this->get('container')->getItems('poll_answer', 'question_id='.$question['id']));
		}
		$respondents = $this->get('container')->getItems('poll_results', 'branch="B"');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Ancor opros site");
		$objPHPExcel->getProperties()->setLastModifiedBy("Ancor opros site");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setDescription("Opros data report Group B");


		// Add some data
		$objPHPExcel->setActiveSheetIndex(0);

		$style = array(
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			)
		);

		$objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($style);

		$firstRow = array('Респондент','','Город','Год');

		foreach($answers as $answer){
			$firstRow[] = join(' ', array($answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch'], $answer['name']));
		}

		// phpexcel add first row

		foreach ($firstRow as $key => $titleCell) {
			$cellCode = $fieldIndexes[$key];
			// set column width
			$objPHPExcel->getActiveSheet()->getColumnDimension($cellCode)->setWidth($key < 3 ? "13" : "30");

			// set text wrap
			$objPHPExcel->getActiveSheet()->getStyle($cellCode.'1:'.$cellCode.$objPHPExcel->getActiveSheet()->getHighestRow())
				->getAlignment()->setWrapText(true);

			$objPHPExcel->getActiveSheet()->SetCellValue($cellCode.'1', $titleCell);
		}

		$excelRowNum = 2;



		foreach ($respondents as $respondent) {
			$answersData = json_decode($respondent['polldata'], true);
			if (is_null($answersData)){
				continue;
			}
			$answersData = array_combine(array_column($answersData,'code'), $answersData);
			$fields = array($respondent['code'], $respondent['branch'], $respondent['answer1'],$respondent['answer2']);

			foreach($answers as $answer){
				$answerData = '';
				if(array_key_exists($answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch'], $answersData)) {
					if (in_array($answer['id'], $answersData[$answer['question_id_value']['item']['code'].$answer['question_id_value']['item']['branch']]['answers'])) {
						$answerData = 1;
					}
				}
				$fields[] = $answerData;
			}

			// phpexcel add data row
			foreach ($fields as $key => $dataCell) {
				$objPHPExcel->getActiveSheet()->SetCellValue($fieldIndexes[$key].$excelRowNum, $dataCell);
			}

			$excelRowNum++;
		}

		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Результаты опроса Группа B');


		// Save Excel 2007 file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($filepath);

		if (!$this->get('fs')->exists($filepath)) {
			throw $this->createNotFoundException('File not found');
		}

		$response = new BinaryFileResponse($filepath);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$filename
		);
		$response->prepare($this->get('request'));

		return $response;
	}

	public function reportbotherAction()
	{
		$respondents = $this->get('container')->getItems('poll_results', 'branch="B"');

		foreach ($respondents as $respondent){
			$answersData = json_decode($respondent['polldata'], true);
			if (is_null($answersData)){
				continue;
			}
			$answersData = array_combine(array_column($answersData,'code'), $answersData);

			if (array_key_exists('9B', $answersData)) {
				if (in_array('112', $answersData['9B']['answers'])) {
					$values = explode(',', $answersData['9B']['value']);
					echo array_pop($values).'<br>'."\n";
				}
			}
		}
	}

	public function reportcAction()
	{
		$fieldIndexes = array(
			'A', 'B', 'C', 'D', 'E', 'F', 'G',
			'H', 'I', 'J', 'K', 'L', 'M', 'N',
			'O', 'P', 'Q', 'R', 'S', 'T', 'U',
			'V', 'W', 'X', 'Y', 'Z'
		);
		$filename = join('_', array('opros_group_c', date('Y_m_d_H_i_s'))).'.xlsx';
		$filepath = PRJ_DIR . '/upload/'.$filename;

		$questions = $this->get('container')->getItems('poll_question', 'publish=1 AND is_last=0');
		$respondents = $this->get('container')->getItems('poll_results', 'branch="C"');

		// Create new PHPExcel object
		$objPHPExcel = new PHPExcel();

		// Set properties
		$objPHPExcel->getProperties()->setCreator("Ancor opros site");
		$objPHPExcel->getProperties()->setLastModifiedBy("Ancor opros site");
		$objPHPExcel->getProperties()->setTitle("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setSubject("Office 2007 XLSX Opros data");
		$objPHPExcel->getProperties()->setDescription("Opros data report Group A");


		// Add some data
		$objPHPExcel->setActiveSheetIndex(0);

		$style = array(
			'alignment' => array(
				'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
			)
		);

		$objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($style);

		$firstRow = array('Респондент','Ветвь','Последний вопрос');

		foreach($questions as $question){
			$firstRow[] = join(' ', array($question['code'].$question['branch'], $question['name']));
		}

		foreach ($firstRow as $key => $titleCell) {
			$cellCode = $fieldIndexes[$key];
			// set column width
			$objPHPExcel->getActiveSheet()->getColumnDimension($cellCode)->setWidth($key < 3 ? "13" : "30");

			// set text wrap
			$objPHPExcel->getActiveSheet()->getStyle($cellCode.'1:'.$cellCode.$objPHPExcel->getActiveSheet()->getHighestRow())
				->getAlignment()->setWrapText(true);

			$objPHPExcel->getActiveSheet()->SetCellValue($cellCode.'1', $titleCell);
		}

		$excelRowNum = 2;

		//todo phpexcel add first row

		foreach ($respondents as $respondent) {
			$answers = json_decode($respondent['polldata'], true);
			$answers = array_combine(array_column($answers,'code'), $answers);
			$fields = array($respondent['code'], $respondent['branch'], $respondent['question']);

			foreach($questions as $question){
				$answer = '-';
				if(array_key_exists($question['code'].$question['branch'], $answers)) {
					$answer = $answers[$question['code'].$question['branch']]['value'];
				}
				$fields[] = $answer;
			}

			//todo phpexcel add data row
			foreach ($fields as $key => $dataCell) {
				$objPHPExcel->getActiveSheet()->SetCellValue($fieldIndexes[$key].$excelRowNum, $dataCell);
			}

			$excelRowNum++;
		}

		// Rename sheet
		$objPHPExcel->getActiveSheet()->setTitle('Результаты опроса Группа C');


		// Save Excel 2007 file
		$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
		$objWriter->save($filepath);

		if (!$this->get('fs')->exists($filepath)) {
			throw $this->createNotFoundException('File not found');
		}

		$response = new BinaryFileResponse($filepath);
		$response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$filename
		);
		$response->prepare($this->get('request'));

		return $response;
	}
} 