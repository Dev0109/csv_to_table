<?php
    if (isset($_POST['fetchData'])) {
        $hostname = "localhost";
        $username = "root";
        $password = "";
        $database = "aca1_db1";

        // Create a connection
        $mysqli = new mysqli($hostname, $username, $password, $database);

        if ($mysqli->connect_error) {
            die("Connection failed: " . $mysqli->connect_error);
        }
    
        $query_batch = "SELECT * FROM p004_nmi_batch";
        $result_batch = $mysqli->query($query_batch);
        if ($result_batch) {
            echo "<table border='1' style='margin-top: 10px'>
            <tr>
                <th>ID</th>
                <th>Site</th>
                <th>DNSP</th>
                <th>Retailer</th>
                <th>CSV File Name</th>
                <th>Description</th>
            </tr>";

            while ($row_batch = $result_batch->fetch_assoc()) {
                $site_name = '';
                $dnsp_name = '';
                $retailer_name = '';

                $site_id = $row_batch['p004_Site_ID'];
                $dnsp_id = $row_batch['p003_DNSP_ID'];
                $retailer_id = $row_batch['p004_Retailer_ID'];

                $query_site = "SELECT * FROM p004_site WHERE id = ?";
                if ($stmt = $mysqli->prepare($query_site)) {
                    $stmt->bind_param("i", $site_id); // "i" represents an integer
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // Access data by column name
                        $site_name = $row['site_name']; // Replace 'column_name' with the actual column name
                    }
                
                    $stmt->close();
                } else {
                    echo "Error in prepared statement: " . $mysqli->error;
                }

                $query_dnsp = "SELECT * FROM p003_dnsp WHERE id = ?";
                if ($stmt = $mysqli->prepare($query_dnsp)) {
                    $stmt->bind_param("i", $dnsp_id); // "i" represents an integer
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // Access data by column name
                        $dnsp_name = $row['dnsp_name']; // Replace 'column_name' with the actual column name
                    }
                
                    $stmt->close();
                } else {
                    echo "Error in prepared statement: " . $mysqli->error;
                }

                $query_retailer = "SELECT * FROM p004_retailer WHERE Id = ?";
                if ($stmt = $mysqli->prepare($query_retailer)) {
                    $stmt->bind_param("i", $retailer_id); // "i" represents an integer
                    $stmt->execute();
                    $result = $stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $row = $result->fetch_assoc();
                        // Access data by column name
                        $retailer_name = $row['retailer_name']; // Replace 'column_name' with the actual column name
                    }
                
                    $stmt->close();
                } else {
                    echo "Error in prepared statement: " . $mysqli->error;
                }

                echo "<tr class='clickable' data-id='" . $row_batch['id'] . "' onclick='batchInfo(" . $row_batch['id'] . ")'>";
                echo "<td>" . $row_batch['id'] . "</td>";
                echo "<td>" . $site_name . "</td>";
                echo "<td>" . $dnsp_name . "</td>";
                echo "<td>" . $retailer_name . "</td>";
                echo "<td>" . $row_batch['NMI_CSV'] . "</td>";
                echo "<td>" . $row_batch['Batch_Description'] . "</td>";
                echo "</tr>";
            }

            echo "</table>";

            $mysqli->close();
        }
    }
?>

<script>
    function batchInfo(id) {
        window.location.href = "batchInfo.php?id=" + id;
    }
</script>