<?php

session_start();

require_once "../config/db.php";

require_once "../engine/ResumeAnalyzer.php";
require_once "../engine/TextFormatter.php";
require_once "../engine/ATSScorer.php";
require_once "../engine/SkillExtractor.php";
require_once "../engine/SuggestionEngine.php";

require_once "SectionDetector.php";

/* ============================================
   Validate Request
============================================ */

if (!isset($_SESSION['user_id'])) {

    header("Location: ../auth/login.php");
    exit();

}

if (!isset($_GET['id'])) {

    die("Resume ID Missing.");

}

$resume_id = intval($_GET['id']);

$user_id = $_SESSION['user_id'];

/* ============================================
   Verify Resume Ownership
============================================ */

$stmt = $conn->prepare("
SELECT *
FROM resumes
WHERE resume_id=?
AND user_id=?
LIMIT 1
");

$stmt->bind_param("ii",$resume_id,$user_id);

$stmt->execute();

$result = $stmt->get_result();

if($result->num_rows==0){

    die("Resume not found or access denied.");

}

$resume = $result->fetch_assoc();

/* ============================================
   Start Database Transaction
============================================ */

$conn->begin_transaction();

try{

    /* ============================================
       Resume Analyzer
    ============================================ */

    $resumeEngine = new ResumeAnalyzer($conn);

    $text = $resumeEngine->getResumeText($resume);

    if(trim($text)==""){

        throw new Exception("Unable to extract resume text.");

    }

    /* ============================================
       Detect Resume Sections
    ============================================ */

    $sections = SectionDetector::detect($text);

    /* ============================================
       ATS Score
    ============================================ */

    $atsEngine = new ATSScorer($sections,$text);

    $atsResult = $atsEngine->calculate();

    $score = $atsResult['score'];

    $report = $atsResult['report'];

    /* ============================================
       Skill Extraction
    ============================================ */

    $skillEngine = new SkillExtractor($text);

    $skills = $skillEngine->extract();

    /* ============================================
       AI Suggestions
    ============================================ */

    $suggestionEngine = new SuggestionEngine(
        $sections,
        $skills,
        $text
    );

    $feedback = $suggestionEngine->generate();

    $strengths = $feedback['strengths'];

    $weaknesses = $feedback['weaknesses'];

    $suggestions = $feedback['suggestions'];

        /* ============================================
       Update Resume Score
    ============================================ */

    $stmt = $conn->prepare("
        UPDATE resumes
        SET ats_score = ?,
            status = 'Analyzed'
        WHERE resume_id = ?
    ");

    if(!$stmt){
        throw new Exception($conn->error);
    }

    $stmt->bind_param("ii",$score,$resume_id);

    if(!$stmt->execute()){
        throw new Exception($stmt->error);
    }

    $stmt->close();



    /* ============================================
       Save Analysis Report
    ============================================ */

    // Remove previous analysis
    $stmt = $conn->prepare("
        DELETE FROM analysis
        WHERE resume_id=?
    ");

    if(!$stmt){
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i",$resume_id);

    if(!$stmt->execute()){
        throw new Exception($stmt->error);
    }

    $stmt->close();



    // Insert latest analysis

    $stmt = $conn->prepare("
        INSERT INTO analysis
        (
            resume_id,
            ats_score,
            strengths,
            weaknesses,
            suggestions
        )
        VALUES
        (
            ?,?,?,?,?
        )
    ");

    if(!$stmt){
        throw new Exception($conn->error);
    }

    $stmt->bind_param(
        "iisss",
        $resume_id,
        $score,
        $strengths,
        $weaknesses,
        $suggestions
    );

    if(!$stmt->execute()){
        throw new Exception($stmt->error);
    }

    $stmt->close();



    /* ============================================
       Save Resume Skills
    ============================================ */

    // Remove old extracted skills

    $stmt = $conn->prepare("
        DELETE FROM resume_skills
        WHERE resume_id=?
    ");

    if(!$stmt){
        throw new Exception($conn->error);
    }

    $stmt->bind_param("i",$resume_id);

    if(!$stmt->execute()){
        throw new Exception($stmt->error);
    }

    $stmt->close();



    /* ============================================
       Insert New Skills
    ============================================ */

    foreach($skills as $skill){

        // Find skill_id

        $stmt = $conn->prepare("
            SELECT skill_id
            FROM skills
            WHERE skill_name=?
            LIMIT 1
        ");

        if(!$stmt){
            throw new Exception($conn->error);
        }

        $stmt->bind_param("s",$skill);

        if(!$stmt->execute()){
            throw new Exception($stmt->error);
        }

        $result = $stmt->get_result();

        if($result->num_rows>0){

            $row = $result->fetch_assoc();

            $skill_id = $row['skill_id'];

            $insert = $conn->prepare("
                INSERT INTO resume_skills
                (
                    resume_id,
                    skill_id
                )
                VALUES
                (
                    ?,?
                )
            ");

            if(!$insert){
                throw new Exception($conn->error);
            }

            $insert->bind_param(
                "ii",
                $resume_id,
                $skill_id
            );

            if(!$insert->execute()){
                throw new Exception($insert->error);
            }

            $insert->close();

        }

        $stmt->close();

    }
       /* ============================================
       Transaction Successful
    ============================================ */

    $conn->commit();

    header("Location: report.php?id=".$resume_id);

    exit();

}

/* ============================================
   Error Handling
============================================ */

catch(Exception $e){

    $conn->rollback();

    echo "

    <!DOCTYPE html>

    <html>

    <head>

        <title>Analysis Failed</title>

        <link rel='stylesheet'
        href='../plugins/bootstrap/css/bootstrap.min.css'>

    </head>

    <body class='bg-light'>

        <div class='container mt-5'>

            <div class='row justify-content-center'>

                <div class='col-md-8'>

                    <div class='card shadow'>

                        <div class='card-header bg-danger text-white'>

                            Analysis Failed

                        </div>

                        <div class='card-body'>

                            <h5>

                            Something went wrong while analyzing the resume.

                            </h5>

                            <hr>

                            <strong>Error Details</strong>

                            <pre>".$e->getMessage()."</pre>

                            <a href='../dashboard/history.php'
                            class='btn btn-primary mt-3'>

                                Back to Resume History

                            </a>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </body>

    </html>

    ";

}