<?php
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['csv_file']['tmp_name'];
    $csvFile = $file;
    $count = 0;

    $html = '<table border="1">';
    $html .= '<thead>';
    $options = array(
        "Ignore", "NMI", "Date", "Interval Length", "Period", "EndTime", "Meter Serial",
        "Kwh", "Generated Kwh", "Net KWh", "Kvarh", "Generated Kvarh", "Net Kvarh", 
        "KVA", "KW", "Daytype", "TimeSlice", "Peak", "Off Peak", "Shoulder"
    );
    
    if (($handle = fopen($csvFile, "r")) !== false) {
        $data = fgetcsv($handle, 1000, ",");
        rewind($handle);
        for ($i=0; $i<count($data); $i++) {
            $html .= '<tr>';
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                
                if ($count < 4) {
                    $html .= '<td>' . $data[$i] . '</td>';
                }

                $count++;

                if($count == 5) {
                    $html .= '<td>';
                    $html .= '<select id="dropdown" name="selected_option" style="border:none">';
                    foreach ($options as $option) {
                        $html .= '<option value="' . $option . '">' . $option . '</option>';
                    }
                    $html .= '</select>';
                    $html .= '</td>';
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
