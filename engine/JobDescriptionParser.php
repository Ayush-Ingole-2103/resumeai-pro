<?php

class JobDescriptionParser
{

    private $conn;
    private $text;

    public function __construct($conn, $text)
    {
        $this->conn = $conn;
        $this->text = strtolower($text);
    }

    public function extractSkills()
    {

        $skills = [];

        $stmt = $this->conn->prepare("
            SELECT skill_name
            FROM skills
            ORDER BY skill_name
        ");

        $stmt->execute();

        $result = $stmt->get_result();

        while($row = $result->fetch_assoc()){

            $skill = strtolower(trim($row['skill_name']));

            if(stripos($this->text, $skill) !== false){

                $skills[] = $row['skill_name'];

            }

        }

        return array_unique($skills);

    }

}