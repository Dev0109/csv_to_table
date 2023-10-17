<?php
if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
    $file = $_FILES['csv_file']['tmp_name'];
    $file_name = $_FILES['csv_file']['name'];
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

    // ... CSV processing code (as in your original code) ...

    // Check for form submission
    if (isset($_POST['submit'])) {

        // Database connection settings
        $hostname = "localhost";
        $username = "root";
        $password = "";
        $database = "aca1_db1";

        // Create a connection
        $mysqli = new mysqli($hostname, $username, $password, $database);

        // Check the connection
        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }

        if (isset($_POST['site_name']) && isset($_POST['dnsp']) && isset($_POST['retailer']) && isset($_POST['description'])) {
            $site_name = $mysqli->real_escape_string($_POST['site_name']);
            $dnsp = $mysqli->real_escape_string($_POST['dnsp']);
            $retailer = $mysqli->real_escape_string($_POST['retailer']);
            $description = $mysqli->real_escape_string($_POST['description']);

            $table_name = "p004_site";
            $site_id = "";
            $dnsp_id = "";
            $retailer_id = "";

            // Insert data into the database
            $sql_site = "INSERT INTO `$table_name` (site_name) VALUES ('$site_name')";

            if ($mysqli->query($sql_site) === TRUE) {
                echo "Record inserted successfully";
            } else {
                echo "Error: " . $sql_site . "<br>" . $mysqli->error;
            }

            $sql_site = "SELECT id FROM p004_site WHERE site_name = ?";
            $stmt = $mysqli->prepare($sql_site);
            $stmt->bind_param("s", $site_name);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $site_id = $row['id'];
                    echo "ID for data value '$site_name' is $site_id";
                } else {
                    echo "No matching data value found.";
                }
            } else {
                echo "Error: " . $stmt->error;
            }

            $sql_dnsp = "SELECT id FROM p003_dnsp WHERE dnsp_name = ?";
            $stmt = $mysqli->prepare($sql_dnsp);
            $stmt->bind_param("s", $dnsp);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $dnsp_id = $row['id'];
                    echo "ID for data value '$dnsp' is $dnsp_id";
                } else {
                    echo "No matching data value found.";
                }
            } else {
                echo "Error: " . $stmt->error;
            }

            $sql_retailer = "SELECT id FROM p004_retailer WHERE retailer_code = ?";
            $stmt = $mysqli->prepare($sql_retailer);
            $stmt->bind_param("s", $retailer);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $retailer_id = $row['id'];
                    echo "ID for data value '$retailer' is $retailer_id";
                } else {
                    echo "No matching data value found.";
                }
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $table_name = "p004_nmi_batch";

            // Insert data into the database
        $sql_site = "INSERT INTO `$table_name` (site_id, dnsp_id, retailer_id, nmi_csv, batch_description) VALUES ('$site_id', '$dnsp_id', '$retailer_id', '$file_name', '$description')";

        if ($mysqli->query($sql_site) === TRUE) {
            echo "Record inserted successfully";
        } else {
            echo "Error: " . $sql_site . "<br>" . $mysqli->error;
        }

        // Close the database connection
        $mysqli->close();
    } else {
        echo "Form not submitted.";
    }
}
?>
