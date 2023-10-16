<?php
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['csv_file']['tmp_name'];
    $csvFile = $file;
    $count = 0;

    $html = '<table border="1">';
    $html .= '<thead>';
    
    if (($handle = fopen($csvFile, "r")) !== false) {
        $data = fgetcsv($handle, 1000, ",");
        rewind($handle);
        for ($i=0; $i<count($data); $i++) {
            $html .= '<tr>';
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                
                if ($count < 5) {
                    $html .= '<td>' . $data[$i] . '</td>';
                }

                $count++;

                if($count == 5) {
                    break;
                }
            }
            rewind($handle);
            $html .= '</tr>';
        }
        if (isset($handle)) {
            fclose($handle);
        }
    }
    
    $html .= '</table>';
    echo $html;
}
?>
