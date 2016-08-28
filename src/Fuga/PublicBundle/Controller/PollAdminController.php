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
			'V', 'W', 'X', 'Y', 'Z'
		);
		$filename = join('_', array('opros_group_a', date('Y_m_d_H_i_s'))).'.xlsx';
		$filepath = PRJ_DIR . '/upload/'.$filename;

		$questions = $this->get('container')->getItems('poll_question', 'publish=1 AND is_last=0 AND (branch="A" OR branch="")');
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
			'V', 'W', 'X', 'Y', 'Z'
		);
		$filename = join('_', array('opros_group_b', date('Y_m_d_H_i_s'))).'.xlsx';
		$filepath = PRJ_DIR . '/upload/'.$filename;

		$questions = $this->get('container')->getItems('poll_question', 'publish=1 AND is_last=0 AND (branch="B" OR branch="C" OR branch="")');
		$respondents = $this->get('container')->getItems('poll_results', 'branch="B"');

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

	public function reportcAction() {
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