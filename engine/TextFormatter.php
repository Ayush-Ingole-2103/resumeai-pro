<?php

class TextFormatter
{
    public static function format($text)
    {
        // Normalize line endings
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // Convert tabs to spaces
        $text = str_replace("\t", " ", $text);

        // Remove duplicate spaces
        $text = preg_replace('/[ ]{2,}/', ' ', $text);

        // Add line breaks before common headings
        $headings = [
            "PROFESSIONAL SUMMARY",
            "SUMMARY",
            "CAREER OBJECTIVE",
            "OBJECTIVE",
            "WORK EXPERIENCE",
            "EXPERIENCE",
            "EMPLOYMENT",
            "EDUCATION",
            "ACADEMIC",
            "TECHNICAL SKILLS",
            "SKILLS",
            "PROJECTS",
            "CERTIFICATIONS",
            "ACHIEVEMENTS",
            "LANGUAGES",
            "INTERESTS"
        ];

        foreach ($headings as $heading) {
            $text = preg_replace(
                '/\s*' . preg_quote($heading, '/') . '\s*/i',
                "\n\n" . $heading . "\n",
                $text
            );
        }

        // Collapse excessive blank lines
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }
}