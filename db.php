<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php 
$db_server = "localhost";
$db_user = "root";
$db_pass = getenv("db_pass");
$db_name = "my_database";

$conn = "";
try {
   $conn = mysqli_connect($db_server,$db_user,$db_pass,$db_name);
} catch (mysqli_sql_exception) {
    echo "not good";
}




?>
</body>
</html>