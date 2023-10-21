<!DOCTYPE html>
<html>
<head>
    <title>NMI Batch Loader</title>
</head>
<body>
    <h1>NMI Batch Loader</h1>
    <form action="" method="post" enctype="multipart/form-data" style="margin-bottom: 10px">
        <h1>Import the CSV File</h1>
        <input type="file" id="fileInput" name="csv_file" accept=".csv">
        <input type="submit" name="submit" value="Upload CSV">
    </form>
    <form action="import.php" method="post" enctype="multipart/form-data">
        <?php if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['csv_file']['tmp_name'];
            $file_name = $_FILES['csv_file']['name'];
            $csvFile = $file;

            $count = 0;

            $html = '<input type="hidden"  name="csv_name" id="csv_name" value="' . $file_name . '">';
            $html .= '<input type="checkbox" name="checkbox_value" checked style="margin-top: 10px; margin-bottom: 10px">';
            $html .= '<table border="1">';
            $html .= '<thead>';
            $options = array(
                "Ignore", "NMI", "Date", "Interval Length", "Period", "EndTime", "Meter Serial",
                "Kwh", "Generated Kwh", "Net KWh", "Kvarh", "Generated Kvarh", "Net Kvarh", 
                "KVA", "KW", "Daytype", "TimeSlice", "Peak", "Off Peak", "Shoulder"
            );
            
            if (($handle = fopen($csvFile, "r")) !== false) {
                $data_1 = fgetcsv($handle, 1000, ",");
                rewind($handle);
                for ($i=0; $i<count($data_1); $i++) {
                    $html .= '<tr">';
                    $count = 0;
                    while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                        
                        if ($count < 4) {
                            $html .= '<td>' . htmlspecialchars($data[$i]) . '</td>';
                        }

                        if ($count >= 4) {
                            $html .= '<td style="display: none">' . htmlspecialchars($data[$i]) . '</td>';
                        }

                        $count++;
                    }

                    $html .= '<td>';
                    $html .= '<select id="dropdown' . $i .'" name="selected_option[]" style="border:none" onChange="loadCsvData()">';
                    foreach ($options as $option) {
                        $html .= '<option value="' . $option . '">' . $option . '</option>';
                    }
                    $html .= '</select>';
                    $html .= '</td>';

                    rewind($handle);
                    $html .= '</tr>';
                }
                if (isset($handle)) {
                    fclose($handle);
                }
            }
            $html .= '<input type="hidden" name="myTableData" id="myTableData">';
            $html .= '</table>';
            echo $html;
        } ?>
        <div>
            <h1>Enter the Site Name</h1>
            <input type="text" name="site_name" required>
        </div>
        <div>
        <?php
            $hostname = "localhost";
            $username = "root";
            $password = "";
            $database = "aca1_db1";

            $mysqli = new mysqli($hostname, $username, $password, $database);

            $query_dnsp = "SELECT * FROM p003_dnsp";

            $result_dnsp = $mysqli->query($query_dnsp);
            $dnsp_array = []; // Corrected variable name

            if ($result_dnsp) {
                if ($result_dnsp->num_rows > 0) {
                    while ($row = $result_dnsp->fetch_assoc()) {
                        array_push($dnsp_array, $row['dnsp_name']);
                    }
                } else {
                    echo "No rows found in the table.";
                }

                $result_dnsp->close();
            } else {
                echo "Query failed: " . $mysqli->error;
            }

            $query_retailer = "SELECT * FROM p004_retailer"; // This should be querying retailer table

            $result_retailer = $mysqli->query($query_retailer);
            $retailer_name = [];
            $retailer_code = [];

            if ($result_retailer) {
                if ($result_retailer->num_rows > 0) {
                    while ($row = $result_retailer->fetch_assoc()) {
                        array_push($retailer_name, $row['retailer_name']);
                        array_push($retailer_code, $row['retailer_code']);
                    }
                } else {
                    echo "No rows found in the table.";
                }

                $result_retailer->close();
            } else {
                echo "Query failed: " . $mysqli->error;
            }

            $mysqli->close();
        ?>
            <h1>Select DNSP</h1>
            <select id="dnsp" name="dnsp" required>
                <?php
                for ($i = 0; $i < count($dnsp_array); $i++) {
                    echo '<option value="' . $dnsp_array[$i] . '">' . $dnsp_array[$i] . '</option>';
                }
                ?>
            </select>
        </div>
        <div>
            <h1>Select Retailer</h1>
            <select id="retailer" name="retailer" required>
                <?php
                for ($i = 0; $i < count($retailer_name); $i++) {
                    echo '<option value="' . $retailer_code[$i] . '">' . $retailer_name[$i] . '</option>';
                }
                ?>
            </select>
        </div>  
        <div>
            <h1>Enter the Description</h1>
            <textarea type="text" name="description" rows="4" cols="50" required></textarea>
        </div>       
        <input type="submit" name="submit" value="Submit" style="margin-top: 10px">
    </form>
    <script>
        function loadCsvData() {
            const table = document.querySelector('table');
            const rows = table.rows;
            const data = [];

            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].cells;
                const rowData = [];

                for (let j = 0; j < cells.length; j++) {
                    if (j == cells.length - 1) {
                        var doc = new DOMParser().parseFromString(cells[j].innerHTML, "text/xml");
                        var select = document.getElementById(`dropdown${i}`).value;
                        rowData.push(select);
                    } else {
                        rowData.push(cells[j].innerText);
                    }
                }

                data.push(rowData);
            }

            const json = JSON.stringify(data);
            document.getElementById('myTableData').value = json;
        }
    </script>
</body>
</html>