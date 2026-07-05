<?php

class ATSScorer
{

    private $sections;

    private $text;

    private $score = 0;

    private $report = [];

    public function __construct($sections, $text)
    {
        $this->sections = $sections;
        $this->text = strtolower($text);
    }

    public function calculate()
    {

        $this->checkContactInfo();
        $this->checkSummary();
        $this->checkExperience();
        $this->checkEducation();
        $this->checkSkills();
        $this->checkProjects();
        $this->checkCertifications();
        $this->checkFormatting();
        $this->checkKeywords();

        return [
            "score"=>$this->score,
            "report"=>$this->report
        ];

    }

    private function addScore($title,$marks,$found)
    {

        if($found)
        {
            $this->score += $marks;
        }

        $this->report[]=[
            "title"=>$title,
            "marks"=>$marks,
            "found"=>$found
        ];

    }

    private function hasContent($section)
    {

        return isset($this->sections[$section]) &&
               trim($this->sections[$section])!="Not Found";

    }

    private function checkContactInfo()
    {

        $email = preg_match("/[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}/i",$this->text);

        $phone = preg_match("/[0-9]{10}/",$this->text);

        $this->addScore("Contact Information",10,($email && $phone));

    }

    private function checkSummary()
    {

        $this->addScore("Professional Summary",10,$this->hasContent("Summary"));

    }

    private function checkExperience()
    {

        $this->addScore("Experience",20,$this->hasContent("Experience"));

    }

    private function checkEducation()
    {

        $this->addScore("Education",10,$this->hasContent("Education"));

    }

    private function checkSkills()
    {

        $this->addScore("Skills",20,$this->hasContent("Skills"));

    }

    private function checkProjects()
    {

        $this->addScore("Projects",10,$this->hasContent("Projects"));

    }

    private function checkCertifications()
    {

        $this->addScore("Certifications",5,$this->hasContent("Certifications"));

    }

    private function checkFormatting()
    {

        $good = substr_count($this->text,"\n") > 20;

        $this->addScore("Formatting",5,$good);

    }

    private function checkKeywords()
    {

        $keywords = [

            "sql",
            "python",
            "java",
            "php",
            "mysql",
            "oracle",
            "aws",
            "html",
            "css",
            "javascript",
            "react",
            "node",
            "git",
            "linux",
            "power bi",
            "tableau"

        ];

        $count=0;

        foreach($keywords as $word)
        {

            if(stripos($this->text,$word)!==false)
            {
                $count++;
            }

        }

        $marks=min(10,$count);

        $this->score += $marks;

        $this->report[]=[
            "title"=>"Keywords",
            "marks"=>$marks,
            "found"=>true
        ];

    }

}