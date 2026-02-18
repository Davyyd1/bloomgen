<?php

namespace App\Services;

class ResumeRedactor
{
    /**
     * Redactează date personale din textul CV-ului.
     * - nume: înlocuit cu inițiale (DI/DIV) dacă e detectat în "Nume:" sau în primele rânduri
     * - email, telefon, CNP, URL, adresă: mascate cu tokenuri
     */
    public function redact(string $text): string
    {
    $text = str_replace(["\r\n", "\r"], "\n", $text);

    // ✅ 1) Nume -> inițiale (ÎNAINTE să redacționezi liniile etichetate)
    $text = $this->replaceNameFromLabelWithInitials($text);
    $text = $this->replaceHeadlineNameWithInitials($text);

    // ✅ 2) Abia acum redacționezi liniile etichetate (fără "nume/prenume")
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

    // 6) Telefon
    $text = preg_replace(
        '/(?x)\b(?:\+?40|0)[\s.\-]*(?:7\d{2}|2\d{2}|3\d{2})[\s.\-]*\d{3}[\s.\-]*\d{3}\b/',
        '[PHONE]',
        $text
    );

    // 7) Adresă
    $text = preg_replace(
        '/(?i)\b(?:str\.?|strada|bd\.?|bulevardul|calea|aleea|intrarea|splaiul|șos\.?|sos\.?|șoseaua|soseaua)\b[\s:,\-]+[^\n,;]{3,90}/u',
        '[ADDRESS]',
        $text
    );

    return $text;
    }


    private function replaceNameFromLabelWithInitials(string $text): string
    {
        return preg_replace_callback(
            '/(?im)^(nume(?:\s+și\s+prenume)?|name|full\s+name)\s*[:\-]\s*([^\n]+)$/u',
            function ($m) {
                $label = $m[1];
                $name  = trim($m[2]);

                // Dacă e deja redactat, nu mai facem nimic
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
        // Luăm doar începutul (unde apare de obicei numele)
        $head = mb_substr($text, 0, 900);
        $tail = mb_substr($text, 900);

        // Căutăm 2-3 cuvinte Capitalized pe prima linie (ex: David Ion / David Ion Vasile)
        $head2 = preg_replace_callback(
    '/(?m)\A(?:\s*\n){0,3}\s*([A-ZĂÂÎȘȚ][A-Za-zĂÂÎȘȚăâîșț\-]+(?:\s+[A-ZĂÂÎȘȚ][A-Za-zĂÂÎȘȚăâîșț\-]+){1,3})\s*(?:\n|$)/u',
    function ($m) {
        $candidate = trim($m[1]);

        $bad = ['Curriculum', 'Vitae', 'Resume', 'CV', 'Europass'];
        foreach ($bad as $b) {
            if (stripos($candidate, $b) !== false) return $m[0];
        }

        $initials = $this->nameToInitials($candidate);
        return str_replace($candidate, $initials, $m[0]);
    },
    $head,
    1
    );
        return $head2 . $tail;
    }

    private function nameToInitials(string $name): string
    {
    // păstrăm litere, spații și "-"
    $name = preg_replace('/[^\p{L}\s\-]/u', ' ', $name);
    $name = preg_replace('/\s+/u', ' ', trim($name));

    // separăm după spațiu
    $parts = preg_split('/\s+/u', $name);

    $initials = '';

    foreach ($parts as $p) {

        // dacă există nume cu "-", îl separăm
        $subParts = explode('-', $p);

        foreach ($subParts as $sp) {
            if ($sp === '') continue;
            $initials .= mb_strtoupper(mb_substr($sp, 0, 1));
        }
    }
    return $initials;
    }
}
