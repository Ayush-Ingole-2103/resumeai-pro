<?php

require_once "../config/db.php";

$file = $_GET['file'];

$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

$text = "";

switch($extension)
{

    case "pdf":

        require_once "../extractors/PDFExtractor.php";

        $text = PDFExtractor::extract($file);

        break;

    case "docx":

        require_once "../extractors/DOCXExtractor.php";

        $text = DOCXExtractor::extract($file);

        break;

    default:

        die("Unsupported File");

}

echo "<pre>";

echo htmlspecialchars($text);

echo "</pre>";