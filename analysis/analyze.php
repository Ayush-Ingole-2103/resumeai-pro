<?php

require_once "../config/db.php";
require_once "../engine/ResumeAnalyzer.php";

if(!isset($_GET['id']))
{
    die("Resume ID Missing");
}

$resume_id = intval($_GET['id']);

$engine = new ResumeAnalyzer($conn);

$resume = $engine->getResume($resume_id);

if(!$resume)
{
    die("Resume not found.");
}

$text = $engine->getResumeText($resume);

echo "<h2>Resume Text</h2>";

echo "<pre>";

echo htmlspecialchars($text);

echo "</pre>";