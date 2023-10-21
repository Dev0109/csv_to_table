<?php
    // Check for form submission
    if (isset($_POST['submit'])) {

        $file_name = $_POST['csv_name'];
        $checkbox =isset($_POST['checkbox_value']) ? $_POST['checkbox_value'] : "";
        // print_r($file_name);
        // Retrieve the table data from the $_POST variable
        $tableData = $_POST['myTableData'];
        $tableArray = json_decode($tableData, true);
        print_r($tableArray);

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
            $batchLatestID = "";

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
                } else {
                    echo "No matching data value found.";
                }
            } else {
                echo "Error: " . $stmt->error;
            }
        }

        $table_name = "p004_nmi_batch";

            // Insert data into the database
        $sql_site = "INSERT INTO `$table_name` (p004_Site_ID, p003_DNSP_ID, p004_Retailer_ID, NMI_CSV, Batch_Description) VALUES ('$site_id', '$dnsp_id', '$retailer_id', '$file_name', '$description')";

        if ($mysqli->query($sql_site) === TRUE) {
            echo "Record inserted successfully";
        } else {
            echo "Error: " . $sql_site . "<br>" . $mysqli->error;
        }

        $sql_batch = "SELECT MAX(id) AS latest_id FROM p004_nmi_batch";
        $result = $mysqli->query($sql_batch);

        if ($result) {
            // Check if any rows were returned
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $batchLatestID = $row['latest_id'];
            } else {
                echo "No rows found in the table.";
            }

            $result->close();
        } else {
            echo "Query failed: " . $mysqli->error;
        }

        $table_name = "p004_nmi_detail";
        insertNMIDetail($mysqli, $table_name, $tableArray, $batchLatestID, $checkbox);

        // Close the database connection
        $mysqli->close();
    } else {
        echo "Form not submitted.";
    }

    function insertNMIDetail ($mysqli, $table_name, $tableArray, $batchLatestID, $checkbox) {
       
        $nmiDetails = array();
        print_r($tableArray);
        $columns=count($tableArray[0]) - 1;
        $rows_ = array_filter($tableArray, function($val) {
            return $val[count($val) -1 ] != 'Ignore';
        });
        // print_r($rows_);
        $data = [];
        $columnsarray = [];
        foreach ($rows_ as $k => $r) {
            $key = $r[count($r)-1];
            array_push($columnsarray, $key);
            // $data[$k] = [];
            for($i=0;$i<$columns;$i++) {
                if(!isset($data[$i]))
                    $data[$i]=[];
                $data[$i][$key ]= $r[$i];
            }
        }
        print_r($columnsarray);

        for($i = 0;$i<count($data);$i++) {
            if ($checkbox == 'on' && $i == 0) {
                print_R("continue");
                continue;
            }  
            $values = [];

            foreach($columnsarray as $key) {
                if (isset($data[$i][$key])){
                    array_push($values, $data[$i][$key]);
                }
                else
                    array_push($values, '');
            }
            
            $values = '"'.implode('","', $values).'"';
            $headers = '`'.implode('`,`', str_replace(' ', '_', $columnsarray)).'`';
            $sql = "INSERT INTO $table_name($headers, p004_nmi_Batch_ID) VALUES ($values, $batchLatestID)";
            if ($mysqli->query($sql) === TRUE) {
                echo "Record inserted successfully";
            } else {
                echo "Error: " . $sql . "<br>" . $mysqli->error;
            }

        }

        /**Save data */


        return;
        
        print_r($data);
    }
?>