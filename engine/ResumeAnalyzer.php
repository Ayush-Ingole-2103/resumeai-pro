<?php

require_once __DIR__ . "/../config/db.php";

class ResumeAnalyzer
{

    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getResume($resume_id)
    {

        $stmt = $this->conn->prepare("
        SELECT *
        FROM resumes
        WHERE resume_id=?
        ");

        $stmt->bind_param("i",$resume_id);

        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();

    }

    public function getResumeText($resume)
    {

        $file = "../".$resume['file_path'];

        if(!file_exists($file))
        {
            die("Resume file not found.");
        }

        $extension = strtolower(pathinfo($file,PATHINFO_EXTENSION));

        switch($extension)
        {

            case "pdf":

                require_once "../extractors/PDFExtractor.php";

                return PDFExtractor::extract($file);

            case "docx":

                require_once "../extractors/DOCXExtractor.php";

                return DOCXExtractor::extract($file);

            default:

                die("Unsupported File Type.");

        }

    }

}