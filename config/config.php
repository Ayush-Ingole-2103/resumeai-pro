<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('SITE_NAME', 'ResumeAI Pro');

define('BASE_URL', 'http://localhost/ai-resume-analyzer');

?>