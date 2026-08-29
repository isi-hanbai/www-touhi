<?php

class helper_Common extends Zend_Controller_Action_Helper_Abstract {

    /**
     * Convert days
     * @param type $strDay
     * @return string
     */
    public function convertDay($strDay) {
        $result = '0000-00-00';
        if ($strDay) {
            $day = strtotime($strDay);
            $result = date('Y', $day) . '-' . date('m', $day) . '-' . date('d', $day);
        }
        return $result;
    }

    /**
     * Get list pagination
     * @return string
     */
    public function getRecodePerpage() {
        $perpages = array(
            10 => '10',
            20 => '20',
            30 => '30',
            50 => '50',
            100 => '100',
        );
        return $perpages;
    }

    /**
     * Format currency
     * @param type $value
     * @param type $type
     * @return type
     */
    public function currencyFormat($value, $type = 0) {
        switch ($type) {
            case 0:
                $value = round($value);
                break;
            case 1:
                $value = round($value / 10, 0) * 10;
                break;
            case 2:
                $value = round($value / 100, 0) * 100;
                break;
            case 3:
                $value = round($value / 1000, 0) * 1000;
                break;
        }
        if ($value != '' and is_numeric($value)) {
            $value = number_format($value, 0, '.', ',');
            //$value = str_replace(',00', '', $value);
        }
        return $value;
    }

    /**
     * Build string params form an array
     * @param type $params
     * @return string
     */
    public function buildStringParams($params = array()) {
        $strParams = '?';
        if (count($params) > 0) {
            foreach ($params as $key => $param) {
                if ($key != 'module' && $key != 'controller' && $key != 'action') {
                    $strParams .= $key . '=' . $param . '&';
                }
            }
            $strParams = trim($strParams, '&');
        }
        return $strParams;
    }
    
    /**
     * Build string params form url
     * @return type
     */
    public function getStringParams() {
        $strParams = '';
        $request = new Zend_Controller_Request_Http();
        $formData = $request->getRequestUri();
        $pos = strpos($formData, '?');
        if ($pos !== false) {
            $strParams = substr($formData, $pos);
        }
        return $strParams;
    }
	
	/**
     * Process export to csv
     * @param type $input_array
     * @param type $output_file_name
     * @param type $delimiter
     */
	function convert_to_csv($input_array, $output_file_name, $delimiter) {
		/** open raw memory as file, no need for temp files */
		$temp_memory = fopen('php://memory', 'w');
		
		/** loop through array  */
		foreach ($input_array as $line) {
			/** default php csv handler * */
			fputcsv($temp_memory, $line, $delimiter);
		}
		
		/** rewrind the "file" with the csv lines * */
		fseek($temp_memory, 0);
		
		/** modify header to be downloadable csv file * */
		header('Content-Type: application/csv');
		header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
		
		/** Send file to browser for download */
		fpassthru($temp_memory);
	}	
	
	function buildFileCsv($data, $filename) {
        include 'library/phpExcel/PHPExcel.php';
        include 'library/phpExcel/PHPExcel/Writer/Excel2007.php';
		
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->setActiveSheetIndex(0);
		
        if (!empty($data)) {
            // Get list keys of array
            $arrKey = array();
            foreach ($data[0] as $key => $value) {
                $arrKey[] = $key;
            }
            // Set header
            $objPHPExcel->getActiveSheet()->fromArray($arrKey, NULL, 'A1');

            // Fill data to row (begin the second row)
            $i = 2;
            foreach ($data as $dt) {
                $objPHPExcel->getActiveSheet()->fromArray($dt, NULL, 'A'.$i);
                $i++;
            }
        }
		header('Content-Type: application/csv');
		header('Content-Disposition: attachement; filename="'. $filename .'";');
				
		$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
		$objWriter->setExcelCompatibility(true);
		$objWriter->save('php://output');
	}
    
    function getJapaneseDay($year, $month, $day) {
        $jDays = array(
            1 => '月',
            2 => '火',
            3 => '水',
            4 => '木',
            5 => '金',
            6 => '土',
            7 => '日',
        );
        
        if ($month < 10) {
            $month = '0'.$month;
        }
        if ($day < 10) {
            $day = '0'. $day;
        }
        $datetime = $year . '-' . $month . '-'. $day;
        $datetimeInt = strtotime($datetime);                

        return $jDays[date('N', $datetimeInt)];
    }
    
    function getJday($day) {
        $jDays = array(
            1 => '月',
            2 => '火',
            3 => '水',
            4 => '木',
            5 => '金',
            6 => '土',
            7 => '日',
        );
        return $jDays[$day];
    }
}