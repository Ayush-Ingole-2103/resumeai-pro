<?php

class JobDescriptionParser
{

    private $text;

    public function __construct($text)
    {
        $this->text = strtolower($text);
    }

    public function extractSkills()
    {

        $masterSkills = [

            "java",
            "python",
            "php",
            "javascript",
            "react",
            "node.js",
            "mysql",
            "mongodb",
            "html",
            "css",
            "git",
            "github",
            "docker",
            "aws",
            "machine learning",
            "data structures",
            "dbms",
            "c",
            "c++"

        ];

        $found = [];

        foreach($masterSkills as $skill){

            if(stripos($this->text,$skill)!==false){

                $found[] = $skill;

            }

        }

        return array_unique($found);

    }

}