<?php

require_once __DIR__ . "/../vendor/autoload.php";
require_once "TextExtractor.php";

use Smalot\PdfParser\Parser;

class PDFExtractor
{

    public static function extract($file)
    {

        $parser = new Parser();

        $pdf = $parser->parseFile($file);

        $text = $pdf->getText();

        return TextExtractor::clean($text);

    }

}