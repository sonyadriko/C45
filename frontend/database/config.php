<?php
$databaseConnection = mysqli_connect("localhost", "root", "", "c45");
if (!$databaseConnection) {
    die("Connection failed: " . mysqli_connect_error());
}
