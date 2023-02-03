<?php

declare (strict_types=1);

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaCompilationTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla sammanställningsfunktioner</h1>";
    $retur .= test_HamtaSammanstallning();
    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testCompilationFunction(string $funktion): string {
    // Testa felaktig ordning på datum
    $svar = hamtaSammanstallning(new DateTimeImmutable(), new DateTimeImmutable("1970-01-01"));
    if ($svar->getStatus() === 400) {
        $retur .= "<p class='ok'>Hämta sammanställning med felaktig ordning på datum returnerade 400 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Hämta sammanställning med felaktig ordning på datum returnerade {$svar->getStatus()} "
                . "istället för 400 som förväntat</p>";
    }

    // Testa utan resultat
    $svar = hamtaSammanstallning(new DateTimeImmutable("1970-01-01"), new DateTimeImmutable("1970-01-01"));
    $retur = "<h2>test_HamtaSammanstallning</h2>";
    if ($svar->getStatus() === 200) {
        if ($svar->getContent()->tasks === []) {
            $retur .= "<p class='ok'>Hämta sammanställning utan poster returnerade 200 och tom array som förväntat</p>";
        } else {
            $retur .= "<p class='error'>Hämta sammanställning utan poster returnerade "
                    . print_r($svar->getContent()->tasks, true)
                    . " istället för tom array som förväntat</p>";
        }
    } else {
        $retur .= "<p class='error'>Hämta sammanställning  utan poster returnerade returnerade {$svar->getStatus()} "
                . "istället för 200 som förväntat</p>";
    }

    // Testa ok
    $svar = hamtaSammanstallning(new DateTimeImmutable("1970-01-01"), new DateTimeImmutable());
    if ($svar->getStatus() === 200) {
            $retur .= "<p class='ok'>Hämta sammanställning returnerade 200 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Hämta sammanställning returnerade {$svar->getStatus()} "
                . "istället för 200 som förväntat</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen hämta en sammmanställning av uppgifter mellan två datum
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaSammanstallning(): string {
    $retur = "<h2>test_HamtaSammanstallning</h2>";
    $retur .= "<p class='ok'>Testar hämta sammanställning mellan två datum</p>";
    return $retur;
}
