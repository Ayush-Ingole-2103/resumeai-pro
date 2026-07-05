<?php

class SuggestionEngine
{

    private $sections;
    private $skills;
    private $text;

    private $strengths = [];
    private $weaknesses = [];
    private $suggestions = [];

    public function __construct($sections, $skills, $text)
    {
        $this->sections = $sections;
        $this->skills = $skills;
        $this->text = strtolower($text);
    }

    public function generate()
    {
        $this->checkContact();
        $this->checkSummary();
        $this->checkExperience();
        $this->checkEducation();
        $this->checkSkills();
        $this->checkProjects();
        $this->checkCertifications();
        $this->checkGithub();
        $this->checkLinkedIn();
        $this->checkResumeLength();
        $this->checkActionVerbs();

        return [
            "strengths" => implode("\n", $this->strengths),
            "weaknesses" => implode("\n", $this->weaknesses),
            "suggestions" => implode("\n", $this->suggestions)
        ];
    }

    /* -----------------------------
       Helper Function
    ------------------------------ */

    private function hasSection($name)
    {
        return isset($this->sections[$name]) &&
               trim($this->sections[$name]) != "";
    }

    /* -----------------------------
       Contact Information
    ------------------------------ */

    private function checkContact()
    {
        $email = preg_match(
            "/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i",
            $this->text
        );

        $phone = preg_match(
            "/[0-9]{10}/",
            preg_replace('/\D/', '', $this->text)
        );

        if($email && $phone){

            $this->strengths[] =
            "Contact information is complete.";

        }else{

            $this->weaknesses[] =
            "Missing phone number or email.";

            $this->suggestions[] =
            "Include a professional email address and phone number.";

        }
    }

    /* -----------------------------
       Summary
    ------------------------------ */

    private function checkSummary()
    {
        if($this->hasSection("Summary")){

            $this->strengths[] =
            "Professional summary is present.";

        }else{

            $this->weaknesses[] =
            "Professional summary not found.";

            $this->suggestions[] =
            "Add a concise professional summary at the beginning of your resume.";

        }
    }

    /* -----------------------------
       Experience
    ------------------------------ */

    private function checkExperience()
    {
        if($this->hasSection("Experience")){

            $this->strengths[] =
            "Experience section detected.";

        }else{

            $this->weaknesses[] =
            "Experience section missing.";

            $this->suggestions[] =
            "Include internships, projects or work experience.";

        }
    }

    /* -----------------------------
       Education
    ------------------------------ */

    private function checkEducation()
    {
        if($this->hasSection("Education")){

            $this->strengths[] =
            "Education section detected.";

        }else{

            $this->weaknesses[] =
            "Education section missing.";

            $this->suggestions[] =
            "Mention your degree, university and graduation year.";

        }
    }

    /* -----------------------------
       Skills
    ------------------------------ */

    private function checkSkills()
    {
        if(count($this->skills)>=5){

            $this->strengths[] =
            "Good technical skills detected.";

        }else{

            $this->weaknesses[] =
            "Very few technical skills found.";

            $this->suggestions[] =
            "Add more technical skills relevant to your target role.";

        }
    }

    /* -----------------------------
       Projects
    ------------------------------ */

    private function checkProjects()
    {
        if($this->hasSection("Projects")){

            $this->strengths[] =
            "Projects section available.";

        }else{

            $this->weaknesses[] =
            "Projects section missing.";

            $this->suggestions[] =
            "Include 2-3 academic or personal projects with measurable outcomes.";

        }
    }

    /* -----------------------------
       Certifications
    ------------------------------ */

    private function checkCertifications()
    {
        if($this->hasSection("Certifications")){

            $this->strengths[] =
            "Certifications detected.";

        }else{

            $this->suggestions[] =
            "Add certifications from Coursera, Udemy, AWS or Microsoft if applicable.";

        }
    }

    /* -----------------------------
       GitHub
    ------------------------------ */

    private function checkGithub()
    {
        if(stripos($this->text,"github")!==false){

            $this->strengths[] =
            "GitHub profile included.";

        }else{

            $this->suggestions[] =
            "Add your GitHub profile to showcase projects.";

        }
    }

    /* -----------------------------
       LinkedIn
    ------------------------------ */

    private function checkLinkedIn()
    {
        if(stripos($this->text,"linkedin")!==false){

            $this->strengths[] =
            "LinkedIn profile detected.";

        }else{

            $this->suggestions[] =
            "Include your LinkedIn profile.";

        }
    }

    /* -----------------------------
       Resume Length
    ------------------------------ */

    private function checkResumeLength()
    {
        $words = str_word_count(strip_tags($this->text));

        if($words < 250){

            $this->weaknesses[] =
            "Resume appears too short.";

            $this->suggestions[] =
            "Expand your experience, projects and achievements.";

        }

        if($words > 900){

            $this->weaknesses[] =
            "Resume appears too long.";

            $this->suggestions[] =
            "Keep your resume concise (1–2 pages).";

        }
    }

    /* -----------------------------
       Action Verbs
    ------------------------------ */

    private function checkActionVerbs()
    {
        $verbs = [
            "developed",
            "designed",
            "implemented",
            "created",
            "improved",
            "optimized",
            "managed",
            "led",
            "built",
            "achieved"
        ];

        $found = 0;

        foreach($verbs as $verb){

            if(stripos($this->text,$verb)!==false){
                $found++;
            }

        }

        if($found >= 3){

            $this->strengths[] =
            "Strong action verbs improve resume impact.";

        }else{

            $this->suggestions[] =
            "Use action verbs like Developed, Designed, Built and Implemented.";

        }
    }

}