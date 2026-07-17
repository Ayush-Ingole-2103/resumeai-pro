<?php

class JobMatcher
{

    private $resumeSkills;
    private $jobSkills;

    public function __construct($resumeSkills, $jobSkills)
    {
        $this->resumeSkills = array_map('strtolower', $resumeSkills);
        $this->jobSkills = array_map('strtolower', $jobSkills);
    }

    public function match()
    {

        // Weight important technical skills higher
        $skillWeights = [
            'python' => 10,
            'java' => 10,
            'php' => 9,
            'javascript' => 9,
            'react' => 9,
            'node.js' => 9,
            'mysql' => 8,
            'mongodb' => 8,
            'docker' => 8,
            'aws' => 10,
            'machine learning' => 10,
            'data structures' => 8,
            'dbms' => 8,
            'git' => 6,
            'github' => 6,
            'html' => 5,
            'css' => 5,
            'c' => 7,
            'c++' => 7
        ];

        $matchedSkills = [];
        $missingSkills = [];
        $extraSkills = [];

        $totalWeight = 0;
        $matchedWeight = 0;

        foreach ($this->jobSkills as $skill) {

            $weight = isset($skillWeights[$skill])
                ? $skillWeights[$skill]
                : 5;

            $totalWeight += $weight;

            if (in_array($skill, $this->resumeSkills)) {

                $matchedSkills[] = $skill;
                $matchedWeight += $weight;

            } else {

                $missingSkills[] = $skill;

            }
        }

        // Extra skills in resume
        foreach ($this->resumeSkills as $skill) {

            if (!in_array($skill, $this->jobSkills)) {

                $extraSkills[] = $skill;

            }
        }

        // Calculate weighted score
        if ($totalWeight == 0) {

            $score = 0;

        } else {

            $score = round(($matchedWeight / $totalWeight) * 100);

        }

        // Verdict
        if ($score >= 90) {

            $verdict = "Excellent Match";
            $color = "success";

        } elseif ($score >= 75) {

            $verdict = "Very Good Match";
            $color = "primary";

        } elseif ($score >= 60) {

            $verdict = "Good Match";
            $color = "warning";

        } elseif ($score >= 40) {

            $verdict = "Average Match";
            $color = "secondary";

        } else {

            $verdict = "Poor Match";
            $color = "danger";

        }

        return [
            'score' => $score,
            'verdict' => $verdict,
            'color' => $color,
            'matched_skills' => $matchedSkills,
            'missing_skills' => $missingSkills,
            'extra_skills' => $extraSkills,
            'matched_count' => count($matchedSkills),
            'required_count' => count($this->jobSkills),
            'matched_weight' => $matchedWeight,
            'total_weight' => $totalWeight
        ];
    }
}