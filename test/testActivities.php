<?php

declare (strict_types=1);
require_once '../src/activities.php';
/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitetAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testActivityFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen test_$funktion finns inte.</p>";
    }
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    try {
        $svar=hamtaAllaAktiviteter();

        //kontrollerar statuskoden
        if(!$svar->getStatus()==200) {
            $retur .="<p class='error'> Felaktig statuskod förväntade 200 fick {$svar->getStatus()}</p>";
        } else {
            $retur .="<p class='ok'>Korrekt statuskod 200</p>";
        }
        //kontrollerar att ingen kategori egenskap är tom
        foreach ($svar->getContent()->activities as $kategori){
            if(!isset($kategori->id)) {
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;   
            }
            if(!isset($kategori->category)) {
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;   
            }

        }
    } catch (Exception $ex) {
        $retur .="<p class='error'>Något gick fel, meddelandet säger:{$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        // Testa negativt tal
        $svar= hamtaEnskildAktivitet(-1);
        if ($svar->getStatus()===400){
            $retur .="<p class='ok'>Hämta enskild med negativt tal ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med negativt tal ger {$svar->getStatus()} "
            . "inte förväntat svar 400</p>";
        }

        // Testa för stort tal
        $svar= hamtaEnskildAktivitet(100);
        if ($svar->getStatus()===400){
            $retur .="<p class='ok'>Hämta enskild med stort tal ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med stort (100) tal ger {$svar->getStatus()} "
            . "inte förväntat svar 400</p>";
        }
        
        // Testa bokstäver
        $svar= hamtaEnskildAktivitet((int) "sju");
        if ($svar->getStatus()===400){
            $retur .="<p class='ok'>Hämta enskild med bokstäver ger förväntat svar 400</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med bokstäver (sju) tal ger {$svar->getStatus()} "
            . "inte förväntat svar 400</p>";
        }
        
        // Testa giltigt tal
        $svar= hamtaEnskildAktivitet(3);
        if ($svar->getStatus()===200){
            $retur .="<p class='ok'>Hämta enskild med 3 ger förväntat svar 200</p>";
        } else {
            $retur .="<p class='error'>Hämta enskild med 3 tal ger {$svar->getStatus()} "
            . "inte förväntat svar 200</p>";
        }


    } catch (Exception $ex){
        $retur .="<p class='error'>Något gick fel, Meddelander säger:<br> {$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitetAktivitet</h2>";
    
    // Testa tom aktivitet
    $aktivitet="";
    $svar=sparaNyAktivitet($aktivitet);
    if ($svar->getStatus()===400) {
        $retur .="<p class='ok'>Spara tom aktivitet misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Spara tom aktivitet returnerade {$svar->getStatus()}
        förväntades 400</p>";
    }
    
    // Testa lägg till
    $db= connectDb();
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar=sparaNyAktivitet($aktivitet);
    if ($svar->getStatus()===200) {
        $retur .="<p class='ok'>Spara tom aktivitet lyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Spara aktivitet returnerade {$svar->getStatus()} förväntades 200</p>";
    }
    $db->rollBack();

    // Testa Lägg till samma
    $db->beginTransaction();
    $aktivitet="Nizze";
    $svar=sparaNyAktivitet($aktivitet); // Spara första gången, borde lyckas
    $svar=sparaNyAktivitet($aktivitet); //faktiskt test, funkar det andra gången
    if ($svar->getStatus()===400) {
        $retur .="<p class='ok'>Spara tom aktivitet två gånger misslyckades som förväntat</p>";
    } else {
        $retur .="<p class='error'>Spara aktivitet två gånger returnerade {$svar->getStatus()} förväntades 400</p>";
    }
    $db->rollBack();
    return $retur;
}

/**
 * Tester för uppdatera Aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";
    
    try {
        // Testa uppdateraAktivitet med ny test i aktivitet
        $db= connectDb();
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID=(int) $nyPost->getContent()->id;//den nya postens id
        $svar=uppdateraAktivitet($uppdateringsID, "Pelle");//prova uppdateraAktivitet
        if($svar->getStatus()===200 && $svar->getContent()->result===true) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet lyckades";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet misslyckades";
            if(isset($svar->getContent()->result)) {
                $retur .=var_export($svar->getContent()->result) . "returnerades istället för förväntat 'true'";
            } else {
                $retur .="{$svar->getStatus()} returnerades istället för förväntat 200";
            }
        }
        $retur .="</p>";
        $db->rollBack();

        // Testa uppdateraAktivitet med samma text i aktivititet
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID=(int) $nyPost->getContent()->id;//den nya postens id
        $svar=uppdateraAktivitet($uppdateringsID, "Nizze");//prova uppdateraAktivitet
        if($svar->getStatus()===200) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet med samma text lyckades som förväntat";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet med samma text returnerade " .
             "{$svar->getStatus()} istället för förväntat 400";
        }
        $retur .="</p>";
        $db->rollBack();
        

        // Testa med tom aktivitet
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID=(int) $nyPost->getContent()->id;//den nya postens id
        $svar=uppdateraAktivitet($uppdateringsID, "");//prova uppdateraAktivitet
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet med tom text misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet misslyckades "
            . "{$svar->getStatus()} returnerades istället för förväntat 400 </p>";
        }
        $retur .="</p>";
        $db->rollBack();

        // Testa med ogiltig id (-1)
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID= -1;//ogiltig id
        $svar=uppdateraAktivitet($uppdateringsID, "Test");//prova uppdateraAktivitet
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet med ogiltigt id (-1) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet med ogiltigt id (-1) returnerade" 
                   ." {$svar->getStatus()} istället för förväntat 400</p>";
        }
        $retur .="</p>";
        $db->rollBack();

        // Testa med obefintligt id (100)
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID= 100;//obefintligt id
        $svar=uppdateraAktivitet($uppdateringsID, "Test");//prova uppdateraAktivitet
        if($svar->getStatus()=== 200 && $svar->getContent()->result===false) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet med obefintligt id (100) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet obefintligt id (100) misslyckades";
            if(isset($svar->getContent()->result)) {
                $retur .=var_export($svar->getContent()->result) . "returnerades istället för förväntat 'true'";
            } else {
                $retur .="{$svar->getStatus()} returnerades istället för förväntat 200";
            }
        }
        $retur .="</p>";
        $db->rollBack();
        
        // Cipis bugg,  Testa med mellanslag som aktivitet
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $uppdateringsID=(int) $nyPost->getContent()->id;//den nya postens id
        $svar=uppdateraAktivitet($uppdateringsID, " ");//prova uppdateraAktivitet
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>UppdateraAktivitet aktivitet med mellanslag som aktivitet, misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>UppdateraAktivitet aktivitet med mellanslag returnerade "
            . "{$svar->getStatus()} istället för förväntat 400 </p>";
        }
        $retur .="</p>";
        $db->rollBack();

    }catch (Exception $ex){
        $db->rollBack();
        if($ex->getCode()===10001){
        $retur .="<p class='error'>uppdateraAktivitet post misslyckades, uppdateraAktivitet går inte att testa!!!</p>";
    } else {
        $retur .="<p class='error'>Fel inträffade:<br>{$ex->getMessage()}</p>";
    }
    }
   return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
try{
        // Testa med ogiltig id (-1)
        $svar=raderaAktivitet(-1);//prova raderaAktivitet
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>raderaAktivitet aktivitet med ogiltigt id (-1) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>raderaAktivitet aktivitet med ogiltigt id (-1) returnerade" 
                   ." {$svar->getStatus()} istället för förväntat 400</p>";
        }

        // Testa med felaktigt id (sju)
        $svar=raderaAktivitet((int)"sju");//prova raderaAktivitet
        if($svar->getStatus()===400) {
            $retur .="<p class='ok'>raderaAktivitet aktivitet med ogiltigt id (sju) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>raderaAktivitet aktivitet med ogiltigt id (sju) returnerade" 
                   ." {$svar->getStatus()} istället för förväntat 400</p>";
        }

        // Testa med obefintligt id (100)
        $svar=raderaAktivitet(100);//prova uppdateraAktivitet
        if($svar->getStatus()=== 200 && $svar->getContent()->result===false) {
            $retur .="<p class='ok'>raderaAktivitet aktivitet med obefintligt id (100) ger förväntat svar 200</p>";
        } else {
            $retur .="<p class='error'>raderaAktivitet aktivitet med obefintligt id (100) returnerade" 
                   ." {$svar->getStatus()} istället för förväntat 400</p>";
        }

        // Testa raderaAktivitet nyskapat id
        $db= connectDb();
        $db->beginTransaction();//gör en transaktion för att inte lägga in massor i databsen
        $nyPost=sparaNyAktivitet("Nizze");//lagar ny post för att leka med
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades", 10001);
        } 
        $nyttID=(int) $nyPost->getContent()->id;//den nya postens id
        $svar=raderaAktivitet($nyttID);
        if($svar->getStatus()===200 && $svar->getContent()->result===true) {
            $retur .="<p class='ok'>raderaAktivitet aktivitet med nyskapat id, lyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>raderaAktivitet aktivitet med nyskapat id, returnerade "
            . "{$svar->getStatus()} istället för förväntat 200 </p>";
        }
        $retur .="</p>";
        $db->rollBack();

    }catch (Exception $ex) {
        $db->rollBack();
        if($ex->getCode()===10001){
        $retur .="<p class='error'>skapa ny aktivitet misslyckades, raderaAktivitet går inte att testa!!!</p>";
    } else {
        $retur .="<p class='error'>Fel inträffade:<br>{$ex->getMessage()}</p>";
    }
    }
    $retur .="</p>";
    return $retur;
}
