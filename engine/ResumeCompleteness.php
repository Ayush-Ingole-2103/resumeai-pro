<?php

class ResumeCompleteness
{

    private $sections;
    private $text;

    public function __construct($sections, $text)
    {
        $this->sections = $sections;
        $this->text = strtolower($text);
    }

    public function calculate()
    {

        $score = 0;

        $report = [];

        /* ===============================
           Name
        =============================== */

        if(strlen(trim($this->text)) > 0){

            $score += 5;
            $report[] = "✓ Name Detected";

        }else{

            $report[] = "✗ Name Missing";

        }

        /* ===============================
           Email
        =============================== */

        if(preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/',$this->text)){

            $score += 5;
            $report[] = "✓ Email Found";

        }else{

            $report[] = "✗ Email Missing";

        }

        /* ===============================
           Phone Number
        =============================== */

        if(preg_match('/(\+91[- ]?)?[6-9][0-9]{9}/',$this->text)){

            $score += 5;
            $report[] = "✓ Phone Number Found";

        }else{

            $report[] = "✗ Phone Number Missing";

        }

        /* ===============================
           Skills
        =============================== */

        if(!empty(trim($this->sections['skills'] ?? ""))){

            $score += 20;
            $report[] = "✓ Skills Section";

        }else{

            $report[] = "✗ Skills Section Missing";

        }

        /* ===============================
           Education
        =============================== */

        if(!empty(trim($this->sections['education'] ?? ""))){

            $score += 15;
            $report[] = "✓ Education Section";

        }else{

            $report[] = "✗ Education Missing";

        }

        /* ===============================
           Experience
        =============================== */

        if(!empty(trim($this->sections['experience'] ?? ""))){

            $score += 20;
            $report[] = "✓ Experience Section";

        }else{

            $report[] = "✗ Experience Missing";

        }

        /* ===============================
           Projects
        =============================== */

        if(!empty(trim($this->sections['projects'] ?? ""))){

            $score += 15;
            $report[] = "✓ Projects Section";

        }else{

            $report[] = "✗ Projects Missing";

        }

        /* ===============================
           Certifications
        =============================== */

        if(
            stripos($this->text,"certification") !== false ||
            stripos($this->text,"certifications") !== false ||
            stripos($this->text,"certificate") !== false
        ){

            $score += 10;
            $report[] = "✓ Certifications Found";

        }else{

            $report[] = "✗ Certifications Missing";

        }

        /* ===============================
           GitHub / LinkedIn
        =============================== */

        if(
            stripos($this->text,"github.com") !== false ||
            stripos($this->text,"linkedin.com") !== false
        ){

            $score += 5;
            $report[] = "✓ Professional Profile Found";

        }else{

            $report[] = "✗ GitHub / LinkedIn Missing";

        }

        return [

            "score"=>$score,

            "report"=>$report

        ];

    }

}