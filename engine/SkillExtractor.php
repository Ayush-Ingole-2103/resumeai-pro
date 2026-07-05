<?php

class SkillExtractor
{

    private $text;

    private $skills = [];

    private $skillDatabase = [

        // Programming Languages
        "PHP",
        "Python",
        "Java",
        "C",
        "C++",
        "C#",
        "JavaScript",
        "TypeScript",

        // Frontend
        "HTML",
        "CSS",
        "Bootstrap",
        "React",
        "Angular",
        "Vue",

        // Backend
        "Node.js",
        "Express",
        "Laravel",
        "CodeIgniter",

        // Databases
        "MySQL",
        "PostgreSQL",
        "MongoDB",
        "Oracle",
        "SQL",
        "PLSQL",

        // Cloud
        "AWS",
        "Azure",
        "GCP",

        // Tools
        "Git",
        "GitHub",
        "Docker",
        "Linux",

        // Data
        "Power BI",
        "Tableau",
        "Excel",
        "ETL",
        "ODI",
        "Informatica",
        "Snowflake",

        // AI
        "Machine Learning",
        "Deep Learning",
        "TensorFlow",
        "PyTorch",
        "OpenAI",
        "LangChain",
        "LLM"

    ];

    public function __construct($text)
    {
        $this->text = strtolower($text);
    }

    public function extract()
    {

        foreach($this->skillDatabase as $skill)
        {

            if(stripos($this->text,$skill)!==false)
            {

                $this->skills[] = $skill;

            }

        }

        $this->skills = array_unique($this->skills);

        sort($this->skills);

        return $this->skills;

    }

}