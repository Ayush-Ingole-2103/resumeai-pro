<?php

class JobMetadataParser
{

    private $text;

    public function __construct($text)
    {
        $this->text = strtolower($text);
    }

    public function parse()
    {

        $metadata = [

            "experience" => "Not Specified",

            "level" => "Not Specified",

            "employment" => "Not Specified",

            "mode" => "Not Specified",

            "education" => "Not Specified"

        ];

        /* ======================================
           Experience Detection
        ====================================== */

        if(preg_match('/(\d+)\+?\s*(year|years)/i',$this->text,$match)){

            $metadata['experience'] = $match[1]."+ Years";

        }
        elseif(stripos($this->text,"fresher")!==false){

            $metadata['experience']="Fresher";

        }
        elseif(stripos($this->text,"internship")!==false){

            $metadata['experience']="Internship";

        }

        /* ======================================
           Job Level
        ====================================== */

        if(stripos($this->text,"intern")!==false){

            $metadata['level']="Intern";

        }
        elseif(stripos($this->text,"junior")!==false){

            $metadata['level']="Junior";

        }
        elseif(stripos($this->text,"mid")!==false){

            $metadata['level']="Mid-Level";

        }
        elseif(stripos($this->text,"senior")!==false){

            $metadata['level']="Senior";

        }

        /* ======================================
           Employment Type
        ====================================== */

        if(stripos($this->text,"full-time")!==false){

            $metadata['employment']="Full-Time";

        }
        elseif(stripos($this->text,"part-time")!==false){

            $metadata['employment']="Part-Time";

        }
        elseif(stripos($this->text,"contract")!==false){

            $metadata['employment']="Contract";

        }
        elseif(stripos($this->text,"internship")!==false){

            $metadata['employment']="Internship";

        }

        /* ======================================
           Work Mode
        ====================================== */

        if(stripos($this->text,"remote")!==false){

            $metadata['mode']="Remote";

        }
        elseif(stripos($this->text,"hybrid")!==false){

            $metadata['mode']="Hybrid";

        }
        elseif(stripos($this->text,"onsite")!==false ||
               stripos($this->text,"on-site")!==false){

            $metadata['mode']="On-site";

        }

        /* ======================================
           Education
        ====================================== */

        $education = [

            "b.tech",
            "be",
            "b.e.",
            "mca",
            "bca",
            "m.tech",
            "msc",
            "bsc",
            "mba"

        ];

        foreach($education as $edu){

            if(stripos($this->text,$edu)!==false){

                $metadata['education']=strtoupper($edu);

                break;

            }

        }

        return $metadata;

    }

}