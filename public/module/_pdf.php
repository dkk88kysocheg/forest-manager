<?php 
/**
 * Вывод PDF файла
 *
 * @package    Forest_Manager
 * @subpackage Forest_Manager/public
 */

require_once (ABSPATH . '/wp-content/vendor/dompdf-2.0.1/autoload.inc.php'); 

use Dompdf\Dompdf;

// Все GET-параметры
$data = Forest_manager_General_Function::output__pdf_file($_GET);

$html ='<html>
<head>
  <style>
    @page { margin-top: 0px; margin-bottom: 0px; margin-left: 0; margin-right: 0; }  
    body { font-size: 12px; font-family: "Open Sans", sans-serif; line-height: 1; } 
    main { padding: 0 60px; }
    .text-center { text-align: center !important; }
    .text-end { text-align: right !important; }
  </style>
</head>
<body>' . $data['content'] . '</body></html>';

$dompdf  = new Dompdf();
$options = $dompdf->getOptions();
$options->set([
  // 'debugLayout' => true,
  'isHtml5ParserEnabled' => true,
  'isRemoteEnabled'      => true
]);
$dompdf->setOptions($options);  
$dompdf->setPaper('A4');
$dompdf->load_html($html);
$dompdf->render();

// Показываем PDF прямо на странице
$dompdf->stream( $data['name_file'] . '.pdf', array("Attachment" => 0) );

// global $_dompdf_warnings;
// $_dompdf_warnings = array();
// global $_dompdf_show_warnings;
// $_dompdf_show_warnings = true;

// header('Content-type: text/plain');
// var_dump($_dompdf_warnings);
// die();



?>  

