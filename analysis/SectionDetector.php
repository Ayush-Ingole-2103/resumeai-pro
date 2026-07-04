<?php

class SectionDetector
{
    public static function detect($text)
    {
        // Normalize text
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text);

        $sections = [
            'summary' => '',
            'experience' => '',
            'education' => '',
            'skills' => '',
            'projects' => '',
            'certifications' => '',
            'languages' => '',
            'others' => ''
        ];

        $current = "others";

        $lines = explode("\n", $text);

        foreach ($lines as $line) {

            $line = trim($line);

            if ($line == "") continue;

            $lower = strtolower($line);

            // SUMMARY
            if (
                strpos($lower, "professional summary") !== false ||
                strpos($lower, "summary") !== false ||
                strpos($lower, "career objective") !== false ||
                strpos($lower, "objective") !== false
            ) {
                $current = "summary";
                continue;
            }

            // EXPERIENCE
            if (
                strpos($lower, "experience") !== false ||
                strpos($lower, "work experience") !== false ||
                strpos($lower, "employment") !== false
            ) {
                $current = "experience";
                continue;
            }

            // EDUCATION
            if (
                strpos($lower, "education") !== false ||
                strpos($lower, "academic") !== false
            ) {
                $current = "education";
                continue;
            }

            // SKILLS
            if (
                strpos($lower, "skills") !== false ||
                strpos($lower, "technical skills") !== false
            ) {
                $current = "skills";
                continue;
            }

            // PROJECTS
            if (
                strpos($lower, "projects") !== false ||
                strpos($lower, "personal projects") !== false
            ) {
                $current = "projects";
                continue;
            }

            // CERTIFICATIONS
            if (
                strpos($lower, "certifications") !== false ||
                strpos($lower, "certificates") !== false
            ) {
                $current = "certifications";
                continue;
            }

            // LANGUAGES
            if (
                strpos($lower, "languages") !== false
            ) {
                $current = "languages";
                continue;
            }

            $sections[$current] .= $line . "\n";
        }

        return $sections;
    }
}