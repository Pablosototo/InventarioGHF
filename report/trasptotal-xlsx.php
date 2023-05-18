<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

include "../core/autoload.php";
include "../core/app/model/ProductData.php";
include "../core/app/model/OperationData.php";
include "../core/app/model/OperationTypeData.php";
include "../core/app/model/SellData.php";
include "../core/app/model/StockData.php";

/** Include PHPExcel */
//require_once dirname(__FILE__) . '/../Classes/PHPExcel.php';
require_once '../core/controller/PHPExcel/Classes/PHPExcel.php';


// Create new PHPExcel object
$objPHPExcel = new PHPExcel();
//$products = ProductData::getAll();
$products = SellData::getAllBySQL(" where operation_type_id=6");

// Set document properties
$objPHPExcel->getProperties()->setCreator("Grupo Hung Fung")
							 ->setLastModifiedBy("Grupo Hung Fung")
							 ->setTitle("Traspasos - Grupo Hung Fung")
							 ->setSubject("Grupo Hung Fung Reporte de Traspasos")
							 ->setDescription("")
							 ->setKeywords("")
							 ->setCategory("");


// Add some data
$sheet = $objPHPExcel->setActiveSheetIndex(0);

$sheet->setCellValue('A1', 'Reporte de Traspasos - Grupo Hung Fung')
->setCellValue('A2', 'Número')
->setCellValue('B2', 'Total')
->setCellValue('C2', 'Almacén')
->setCellValue('D2', 'Fecha');
//->setCellValue('E2', 'Por Entregar');

$start = 3;

foreach($products as $sell){

	$operations = OperationData::getAllProductsBySellId($sell->id);

	 $ARR = [];

	foreach ($operations as $operation) {
		if ($operation->operation_type_id == 2) {
			$total = ($operation->q * $operation->price_in);
			$resultado = array_push($ARR, $total);
			$suma2 = array_sum(($ARR));
		}
	}

$sheet
->setCellValue('A'.$start, $sell->id)
->setCellValue('B'.$start, Core::$symbol . " " . number_format($suma2, 2, ",", "."))
->setCellValue('C'.$start,  $sell->getStockTo()->name)
->setCellValue('D'.$start,  $sell->created_at);

$start++;

}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="inventary-'.time().'.xlsx"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header ('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
exit;