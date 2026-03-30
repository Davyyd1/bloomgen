<?php

namespace App\Services;

class ResumeRedactor
{
    /**
     * Redacts personal informations from the resume text.
     * - name: replaced with initials (DMD/AMS) if it is detected in "Nume:" or first rows
     * - email, phone number, CNP, URL, address: anonymized
     */
    public function redact(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        // 1) Name -> initials
        $text = $this->replaceNameFromLabelWithInitials($text);
        $text = $this->replaceHeadlineNameWithInitials($text);

        // 2) Phone
        $text = preg_replace(
            '/(?im)^(telefon|tel|mobil|email|adres[ăa]|address|location|localitate|oraș|oras|judet|județ)\s*[:\-]\s*.+$/u',
            '$1: [REDACTED]',
            $text
        );

        // 3) Email
        $text = preg_replace(
            '/(?i)\b[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}\b/',
            '[EMAIL]',
            $text
        );

        // 4) URL
        $text = preg_replace(
            '/(?i)\b(?:https?:\/\/|www\.)\S+\b/',
            '[URL]',
            $text
        );

        // 5) CNP
        $text = preg_replace(
            '/\b[1-9]\d{12}\b/',
            '[CNP]',
            $text
        );

        // 6) Phone
        $text = preg_replace(
            '/\+?\d{1,3}[\s.-]?(?:\d[\s.-]?){6,14}\d/',
            '[PHONE]',
            $text
        );

        // 7) Address — RO + international
        $text = preg_replace(
            '/(?i)\b(?:'
            // romanian
            . 'str\.?|strada|bd\.?|bulevardul|calea|aleea|intrarea|splaiul|șos\.?|sos\.?|șoseaua|soseaua'
            // deutch
            . '|stra[sß]e|str\.|gasse|weg|platz|allee|ring|damm|ufer'
            // english
            . '|street|st\.|avenue|ave\.?|road|rd\.?|lane|ln\.?|drive|dr\.?|boulevard|blvd\.?|court|ct\.?|place|pl\.?|terrace|ter\.?|way|close|crescent'
            // french
            . '|rue|avenue|boulevard|impasse|allée|chemin'
            // spanish / italian
            . '|calle|avenida|carrera|via|viale|corso|vicolo|piazza'
            . ')\b[\s:,\-]+[^\n,;]{3,90}/u',
            '[ADDRESS]',
            $text
        );

        return $text;
    }


    private function replaceNameFromLabelWithInitials(string $text): string
    {
        return preg_replace_callback(
            '/(?im)^(nume(?:\s+și\s+prenume)?|prenume|name|full\s+name|first\s+name|last\s+name|vorname|nachname|nom|prénom)\s*[:\-]\s*([^\n]+)$/u',
            function ($m) {
                $label = $m[1];
                $name  = trim($m[2]);

                // if it is already "REDACTED", do nothing
                if (stripos($name, '[REDACTED]') !== false) {
                    return $m[0];
                }

                $initials = $this->nameToInitials($name);
                return $label . ': ' . $initials;
            },
            $text
        );
    }

    private function replaceHeadlineNameWithInitials(string $text): string
    {
        $lines    = explode("\n", $text);
        $nonEmpty = array_values(array_filter($lines, fn($l) => trim($l) !== ''));


        // words that can't be a person name (blacklist)
        $notAName = [
            'Microsoft', 'Excel', 'Word', 'PowerPoint', 'Outlook', 'Office',
            'Figma', 'Canva', 'Adobe', 'Photoshop', 'AutoCAD', 'Google', 'Linux',
            'Windows', 'Oracle', 'Salesforce', 'Software', 'Hardware',
            'Certifications', 'Certification', 'Skills', 'Aptitudini', 'Studii',
            'Experiență', 'Experienta', 'Experience', 'Education', 'Educație',
            'Summary', 'Profile', 'Profil', 'References', 'Referințe',
            'Curriculum', 'Vitae', 'Resume', 'Europass', 'Page',
            'România', 'Romania', 'Bulgaria', 'Moldova',
            'Recruiter', 'Developer', 'Manager', 'Engineer', 'Specialist',
            'Intern', 'Director', 'Analyst', 'Consultant', 'Administrator',
        ];

        $notANameLower = array_map('strtolower', $notAName);

        for ($i = 0; $i < min(15, count($nonEmpty)); $i++) {
            $line = $nonEmpty[$i];

            if (!preg_match(
                '/^[ \t]*([A-ZĂÂÎȘȚ][A-Za-zĂÂÎȘȚăâîșț\-]+(?:[ \t]+[A-ZĂÂÎȘȚ][A-Za-zĂÂÎȘȚăâîșț\-]+){1,3})[ \t]*$/u',
                $line,
                $m
            )) {
                continue;
            }

            $candidate = trim($m[1]);
            $words     = preg_split('/\s+/u', $candidate);

            if (count($words) < 2) continue;

            // if the word selected is one from the blacklist, skip
            $isName = true;
            foreach ($words as $w) {
                if (in_array(strtolower($w), $notANameLower, true)) {
                    $isName = false;
                    break;
                }
            }
            if (!$isName) continue;

            $initials = $this->nameToInitials($candidate);
            $text = preg_replace(
                '/(?m)^[ \t]*' . preg_quote($candidate, '/') . '[ \t]*$/u',
                $initials,
                $text,
                1
            );

            break;
        }

        return $text;
    }

    private function nameToInitials(string $name): string
    {
        // we keep letters, space and "-"
        $name = preg_replace('/[^\p{L}\s\-]/u', ' ', $name);
        $name = preg_replace('/\s+/u', ' ', trim($name));

        // separate from space
        $parts = preg_split('/\s+/u', $name);

        $initials = '';

        foreach ($parts as $p) {

            // if there is a name with "-" we split
            $subParts = explode('-', $p);

            foreach ($subParts as $sp) {
                if ($sp === '') continue;
                $initials .= mb_strtoupper(mb_substr($sp, 0, 1));
            }
        }
        return $initials;
    }

    public function extractInitials(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", $text);

        if (preg_match('/(?im)^(?:nume(?:\s+și\s+prenume)?|prenume|name|full\s+name|first\s+name|last\s+name)\s*[:\-]\s*([A-ZĂÂÎȘȚ]{1,8})\s*$/u', $text, $m)) {
            return trim($m[1]);
        }

        foreach (explode("\n", $text) as $line) {
            $line = trim($line);
            if (preg_match('/^[A-ZĂÂÎȘȚ]{2,8}$/u', $line)) {
                return $line;
            }
        }

        return '';
    }
}
