<!DOCTYPE html>
<html>
<head>
    <title>NMI Batch Loader</title>
</head>
<body>
    <h1>NMI Batch Loader</h1>
    <form action="import.php" method="post" enctype="multipart/form-data">
        <div>
            <h1>CSV File Import</h1>
            <input type="file" name="csv_file" accept=".csv" required>
        </div>
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
</body>
</html>
