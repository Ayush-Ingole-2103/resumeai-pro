<?php

class TextExtractor
{
    public static function clean($text)
    {
        // Remove extra spaces
        $text = preg_replace('/\s+/', ' ', $text);

        // Remove invisible characters
        $text = preg_replace('/[[:^print:]]/', '', $text);

        return trim($text);
    }
}