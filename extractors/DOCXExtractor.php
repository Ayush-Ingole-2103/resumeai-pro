<?php

require_once "TextExtractor.php";

class DOCXExtractor
{

    public static function extract($file)
    {

        $zip = new ZipArchive;

        $text = "";

        if ($zip->open($file) === TRUE)
        {

            if (($index = $zip->locateName("word/document.xml")) !== false)
            {

                $data = $zip->getFromIndex($index);

                $text = strip_tags($data);

            }

            $zip->close();

        }

        return TextExtractor::clean($text);

    }

}