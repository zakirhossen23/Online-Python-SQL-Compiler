<?php session_start(); ?>
<?php

function DeleteFolder($path) {
    if (is_dir($path) === true) {
        $files = array_diff(scandir($path), array('.', '..'));

        foreach ($files as $file) {
            DeleteFolder(realpath($path) . '/' . $file);
        }

        return rmdir($path);
    } else if (is_file($path) === true) {
        return unlink($path);
    }

    return false;
}

// //Remove Files folder first then save a new file
// DeleteFolder("files");
if (!file_exists("files/sql")) {
    mkdir('files/sql', 0777, true);
}

if (!file_exists("files/python")) {
    mkdir('files/python', 0777, true);
}


$code = $_POST['code'];
$input = $_POST['input'];
if ($_POST['type'] === "python") {

    //input save in file
    $inputfilepath = "files/python/input.txt";
    $inputfile = fopen($inputfilepath, "w");
    fwrite($inputfile, $input);
    fclose($inputfile);


    //code data save in file
    if (!isset($_SESSION["pythonFile"])) {
        $filename = substr(md5(mt_rand()), 0, 5);
        $codepath = "files/python/" . $filename . "." . "py";
        $_SESSION["pythonFile"] =  $codepath;
    } else {
        $codepath = $_SESSION["pythonFile"];
    }
    $codefile = fopen($codepath, "w");
    fwrite($codefile, $code);
    fclose($codefile);

    $commond = "python $codepath 2>&1" . "<" . "files/python/input.txt";
    $output = shell_exec($commond);

    echo $output;
} else {
    function is_write_type($sql) {
        return (bool) preg_match('/^\s*"?(SET|INSERT|UPDATE|DELETE|REPLACE|CREATE|DROP|TRUNCATE|LOAD|COPY|ALTER|RENAME|GRANT|REVOKE|LOCK|UNLOCK|REINDEX|MERGE)\s/i', $sql);
    }



    //code data save in file
    if (!isset($_SESSION["sqlFile"])) {
        $filename = substr(md5(mt_rand()), 0, 5);
        $codepath = "files/sql/" . $filename . "." . "sqlite";
        $_SESSION["sqlFile"] =  $codepath;
    } else {
        $codepath = $_SESSION["sqlFile"];
    }
    $inputfile = fopen($codepath, "w");
    fclose($inputfile);

    $database = new SQLite3($codepath);
    $re = '/^[^;]+[(),\'":*.0-9-A-Z-a-z \n]+;/m';

    preg_match_all($re, $code, $matches);

    $columnHTML = "";
    $dataRows = "";
    for ($i = 0; $i < Count($matches[0]); $i++) {
        $sql_query = $matches[0][$i];
        if (!is_write_type($sql_query)) {
            $results =  $database->query($sql_query);

            while ($row = $results->fetchArray()) {
                $keys = (array_keys((array)$row));
                $totalColumn = ($keys[Count($keys) - 2]);
                $columnHTML="";
                for ($i2 = 0; $i2 < Count($keys); $i2++) {
                    if ($i2 % 2 != 0) {
                        $columnHTML =  $columnHTML . "<th>" . $keys[$i2] . "</th>";
                    }
                }
                $dataRows = $dataRows . "<tr>";
                for ($i2 = 0; $i2 < $totalColumn + 1; $i2++) {
                    $dataRows = $dataRows . "<td>" . $row[$i2] . "</td>";
                }
                $dataRows = $dataRows . "</tr>";
            }
        } else {
            $database->exec($sql_query);
        }
    }
?>

    <table class="table table-striped">
        <thead>
            <tr>
                <?php echo $columnHTML ?>
            </tr>
        </thead>
        <tbody>
            <?php echo $dataRows ?>
        </tbody>
    </table>
<?php
}
