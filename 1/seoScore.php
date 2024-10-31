<?php

function seoScore(string $html): int
{
    $score = 0;

    if (preg_match('/<title>(.*)<\/title>/', $html, $matches))
    {
        $title = trim($matches[1]);
        $score += strlen($title) > 60 ? 7 : 10;
    }

    if (preg_match('/<meta\s+name=[\'"]description[\'"]\s+content=[\'"](.*)[\'"]\s*>/', $html, $matches))
    {
        $description = trim($matches[1]);
        $score += strlen($description) <= 160 ? 10 : 7;
    }

    if (preg_match('/<meta\s+name=[\'"]viewport[\'"]\s+content=[\'"](.*)[\'"]\s*>/', $html, $matches))
    {
        $score += 10;
    }

    if (preg_match('/<meta\s+name=[\'"]robots[\'"]\s+content=[\'"](.*)[\'"]\s*>/', $html, $matches))
    {
        $score += 10;
    }

    if (preg_match('/<meta\s+charset=[\'"](.*)[\'"]\s*>/', $html, $matches))
    {
        $score += 10;
    }

    if (preg_match_all('/<h1>/', $html, $matches))
    {
        $score += count($matches[0]) == 1 ? 10 : 7;
    }

    foreach ([
        'h2' => 5,
        'h3' => 5,
        'footer' => 10,
        'header' => 10,
        'section' => 10,
     ] as $tag => $sc)
    {
        if (str_contains($html, "<{$tag}>"))
        {
            $score += $sc;
        }
    }

    return $score;
}
