<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/tasks.php';
/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    $retur .= test_KontrolleraIndata();
    return $retur;
}

/**
 * Funktion för att testa en enskild funktion
 * @param string $funktion namnet (utan test_) på funktionen som ska testas
 * @return string html-sträng med information om resultatet av testen eller att testet inte fanns
 */
function testTaskFunction(string $funktion): string {
    if (function_exists("test_$funktion")) {
        return call_user_func("test_$funktion");
    } else {
        return "<p class='error'>Funktionen $funktion kan inte testas.</p>";
    }
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaUppgifterSida(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try {
    // Testa hämta felaktigt sidnummer (-1) => 400
    $svar= hamtaSida(-1);
    if($svar->getStatus()===400){
        $retur .="<p class='ok'>Hämta felaktigt sidnummer (-1) gav förväntat svar 400</p>";
    } else {
        $retur .="<p class='error'>Hämta felaktigt sidnummer (-1) gav {$svar->getStatus()} 
        förväntat svar 400</p>";
    }
    // Testa hämta giltigt sidnummer (1) => 200 + rätt egeneskaper
    $svar= hamtaSida(1);
    if($svar->getStatus()!==200){
        $retur .="<p class='error'>Hämta Giltigt sidnummer (1) gav {$svar->getStatus()}
         istället för förväntat svar 200</p>";
    } else {
        $retur .="<p class='ok'>Hämta giltigt sidnummer (1) gav förväntat svar 200</p>";
        $result=$svar->getContent()->tasks;
        foreach ($result as $task) {
            if(!isset($task->id)) {
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;   
            }
            if(!isset($task->activityId)) {
                $retur .="<p class='error'>Egenskapen activityId saknas</p>";
                break;   
            }
            if(!isset($task->activity)) {
                $retur .="<p class='error'>Egenskapen activity saknas</p>";
                break;   
            }
            if(!isset($task->date)) {
                $retur .="<p class='error'>Egenskapen date saknas</p>";
                break;   
            }
            if(!isset($task->time)) {
                $retur .="<p class='error'>Egenskapen time saknas</p>";
                break;   
            }
        }
    }

    // Testa hämta för stor sidnummer => 200 + tom array
    $svar= hamtaSida(100);
    if($svar->getStatus()!==200){
        $retur .="<p class='error'>Hämta för stort sidnummer (100) gav {$svar->getStatus()}
         istället för förväntat svar 200</p>";
    }else {
        $retur .="<p class='ok'>Hämta för stort sidnummer (100) gav förväntat svar 200</p>";
        $resultat=$svar->getContent()->tasks;
        if(!$resultat===[]) {
            $retur .="<p class='error'>Hämta för stort sidnummer (100) ska inehålla en tom array för tasks <br>"
            . print_r($resultat, true) . "<br> returnerades</p>";   
        }
    }
        } catch (Exception $ex) {
        $retur .="<p class='error'>Något gick fel, meddelandet säger:{$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    // Testa fel ordning på datum => 400
    $datum1=new DateTimeImmutable();
    $datum2=new DateTime("yesterday");
    $svar= hamtaDatum($datum1, $datum2);
    if($svar->getStatus()===400){
        $retur .="<p class='ok'>Hämta fel ordning på datum gav förväntat svar 400</p>";
    } else {
        $retur .="<p class='error'>Hämta fel ordning på datum gav {$svar->getStatus()}
         istället för förväntat svar 400</p>";
    }

    // Testa datum utan poster => 200 och tomm array
    $datum1=new DateTimeImmutable("1970-01-01");
    $datum2=new DateTimeImmutable("1970-01-02");
    $svar= hamtaDatum($datum1, $datum2);
    if($svar->getStatus()!==200){
        $retur .="<p class='error'>Hämta Datum (1970-01-01-1970-01-02) utan poster gav {$svar->getStatus()}
         istället för förväntat svar 200</p>";
    } else {
        $retur .="<p class='ok'>Hämta Datum (1970-01-01-1970-01-02) utan poster gav förväntat svar 200</p>";
        $resultat=$svar->getContent()->tasks;
        if(!$resultat===[]) {
            $retur .="<p class='error'>Hämta Datum (1970-01-01-1970-01-02) utan poster ska inehålla en tom array för tasks <br>"
            . print_r($resultat, true) . "<br> returnerades</p>";   
        }
    }
    // Testa giltiga datum med poster => 200 med saker i en array
    $datum1=new DateTimeImmutable("2005-06-17");
    $datum2=new DateTimeImmutable();
    $svar= hamtaDatum($datum1, $datum2);
    if($svar->getStatus()!==200){
        $retur .="<p class='error'>Hämta Datum med poster gav {$svar->getStatus()}
         istället för förväntat svar 200</p>";
    } else {
        $retur .="<p class='ok'>Hämta Datum med poster (2005-06-17 -  {$datum2->format('Y-m-d')}) 
        gav förväntat svar 200</p>";
        $resultat=$svar->getContent()->tasks;
        foreach ($resultat as $task) {
            if(!isset($task->id)) {
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;   
            }
            if(!isset($task->activityId)) {
                $retur .="<p class='error'>Egenskapen activityId saknas</p>";
                break;   
            }
            if(!isset($task->activity)) {
                $retur .="<p class='error'>Egenskapen activity saknas</p>";
                break;   
            }
            if(!isset($task->date)) {
                $retur .="<p class='error'>Egenskapen date saknas</p>";
                break;   
            }
            if(!isset($task->time)) {
                $retur .="<p class='error'>Egenskapen time saknas</p>";
                break;   
            }
        }
    }
    return $retur;
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try {
    // Testa hämta felaktigt sidnummer (-1) => 400
        $svar= hamtaSida(-1);
        if($svar->getStatus()===400) {
            $retur .= "<p class='ok'>Hämta felaktigt sidnummer (-1) gav förväntat svar 400</p>";
        } else {
            $retur .= "<p class='error'>Hämta felaktigt sidnummer (-1) gav {$svar->getStatus()} "
            . "istället för förväntat svar 400</p>";
        }
    
    // Testa hämta giltigt sidnummer (1) => 200 + rätt egenskaper
    $svar=hamtaSida(1);
    if($svar->getStatus()!==200) {
            $retur .= "<p class='error'>Hämta giltigt sidnummer (1) gav {$svar->getStatus()} "
            . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'>Hämta giltigt sidnummer (1) gav förväntat svar 200</p>";
        $result=$svar->getContent()->tasks;
        foreach ($result as $task) {
            if(!isset($task->id)) {
                $retur .="<p class='error'>Egenskapen id saknas</p>";
                break;
            }
            if(!isset($task->activityId)) {
                $retur .="<p class='error'>Egenskapen activityId saknas</p>";
                break;
            }
            if(!isset($task->activity)) {
                $retur .="<p class='error'>Egenskapen activity saknas</p>";
                break;
            }
            if(!isset($task->date)) {
                $retur .="<p class='error'>Egenskapen date saknas</p>";
                break;
            }
            if(!isset($task->time)) {
                $retur .="<p class='error'>Egenskapen time saknas</p>";
                break;
            }
        }
    }
    // Testa hämta för stor sidnr => 200 + tom array
    $svar=hamtaSida(100);
    if ($svar->getStatus()!==200) {
            $retur .= "<p class='error'>Hämta för stort sidnummer (100) gav {$svar->getStatus()} "
            . "istället för förväntat svar 200</p>";
    } else {
        $retur .= "<p class='ok'>Hämta för stort sidnummer (100) gav förväntat svar 200</p>";
        $resultat=$svar->getContent()->tasks;
        if(!$resultat===[]) {
            $retur .= "<p class='error'>Hämta för stort sidnummer ska innehålla en tom array för tasks<br>"
                    . print_r($resultat, true) . " <br>returnerades</p>";
        } 
    }
    
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }
    return $retur;
}
/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";
    try {
        // Testa skapa ny post => 200
        $igar=new DateTimeImmutable("yesterday");
        $postData=["date"=>$igar->format('Y-m-d'),
            "time"=>"05:00",
            "activityId"=>1,
            "description"=>"Hurra vad bra"];
        $db= connectDb();
        $db->beginTransaction();
        $svar= sparaNyUppgift($postData);
        if($svar->getStatus()==200){
            $retur .="<p class='ok'> Spara ny uppgift lyckades</p>";
        } else {
            $retur .="<p class='error'> Spara ny uppgift misslyckades {$svar->getStatus()} 
                     returnerades istället för förväntat 200</p>";
        }
        $db->rollBack();
    } catch (Exception $ex){
        $retur .=$ex->getMessage();
    }
    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";
    try{
    //testa ogiltigt id
    $db= connectDb();
    $db->beginTransaction();
    $nyPostData=["date"=>date('Y-m-d'),//ny postdata
                "time"=>"06:00",
                "activityId"=>2];
    $svar= uppdateraUppgift(-1, $nyPostData);
    if ($svar->getStatus()===400) {
        $retur .= "<p class='ok'>uppdatera uppgift med ogiltig id returnerade 400 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>uppdatera uppgift med ogiltig id returnerade {$svar->getStatus()} istället för förväntat 400</p>";
    }
    $db->rollBack();

    // Testa id som inte finns post
    $igar=new DateTimeImmutable("yesterday");
    $postData=["date"=>$igar->format('Y-m-d'),
        "time"=>"05:00",
        "activityId"=>1,
        "description"=>"Hurra vad bra"];
    $db= connectDb();
    $db->beginTransaction();
    $nyPost=sparaNyUppgift($postData);
    if ($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades");
    } 
    $nyttID=(int) $nyPost->getContent()->id;//den nya postens id
    raderaUppgift($nyttID);// Tar bort posten så det inte finns en post på nyttID
    $nyPostData=["date"=>date('Y-m-d'),//ny postdata
                "time"=>"06:00",
                "activityId"=>2];
    $svar= uppdateraUppgift($nyttID, $nyPostData);
    if ($svar->getStatus()===200) {
        if($svar->getContent()->result===false){
        $retur .= "<p class='ok'>uppdatera uppgift med tom post returnerade 200 som förväntat</p>";
    } else{
        $retur .= "<p class='error'>uppdatera uppgift tom post returnerade true istället för förväntat false</p>";
    }} else {
        $retur .= "<p class='error'>uppdatera uppgift tom post returnerade {$svar->getStatus()} istället för förväntat 200</p>";
    }
        $db->rollBack();
    
    //testa allt ok
        $postData=["date"=>date('Y-m-d'),
                    "time"=>"05:00",
                    "activityId"=>1];
        $db= connectDb();
        $db->beginTransaction();
        $nyPost=sparaNyUppgift($postData);
        if ($nyPost->getStatus()!==200) {
            throw new Exception("Skapa ny post misslyckades");
        } 
        $nyttID=(int) $nyPost->getContent()->id;//den nya postens id
        $nyPostData=["date"=>date('Y-m-d'),//ny postdata
                    "time"=>"06:00",
                    "activityId"=>2];
        $svar= uppdateraUppgift($nyttID, $nyPostData);
        if ($svar->getStatus()===200) {
            if($svar->getContent()->result===true){
            $retur .= "<p class='ok'>uppdatera uppgift med giltig post returnerade 200 som förväntat</p>";
        } else{
            $retur .= "<p class='error'>uppdatera uppgift returnerade false istället för förväntat true</p>";
        }} else {
            $retur .= "<p class='error'>uppdatera uppgift returnerade {$svar->getStatus()} istället för förväntat 200</p>";
        }
        $db->rollBack();
    }catch (Exception $ex){
        $retur .=$ex->getMessage();
    }
    return $retur;
}
/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";
    try {
    // Testa ogiltig tal (-1) == 400
    $svar= raderaUppgift(-1);
    if ($svar->getStatus()===400) {
        $retur .= "<p class='ok'>Radera uppgift med ogiltigt tal returnerade 400 som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Radera uppgift returnerade {$svar->getStatus()} istället för förväntat 400</p>";
    }
    // Testa ta bort post som finns == 200
        $postData=["date"=>date('Y-m-d'),
            "time"=>"05:00",
            "activityId"=>1];
    $db= connectDb();
    $db->beginTransaction();
    $nyPost=sparaNyUppgift($postData);
    if ($nyPost->getStatus()!==200) {
        throw new Exception("Skapa ny post misslyckades");
    } 
    $nyttID=(int) $nyPost->getContent()->id;//den nya postens id

    $svar= raderaUppgift($nyttID);
    if ($svar->getStatus()===200) {
        if($svar->getContent()->result===true){
        $retur .= "<p class='ok'>Radera uppgift med giltig post returnerade 200 som förväntat</p>";
    } else{
        $retur .= "<p class='error'>Radera uppgift returnerade false istället för förväntat true</p>";
    }} else {
        $retur .= "<p class='error'>Radera uppgift returnerade {$svar->getStatus()} istället för förväntat 200</p>";
    }
    $db->rollBack();

    // Testa ta bort post som inte finns == 200 false
    $svar= raderaUppgift($nyttID);
    if ($svar->getStatus()===200) {
        if($svar->getContent()->result===false){
        $retur .= "<p class='ok'>Radera uppgift med post som inte finns returnerade 200 som förväntat</p>";
    } else{
        $retur .= "<p class='error'>Radera uppgift med post som inte finns returnedae true istället för förväntat false</p>";
    }} else {
        $retur .= "<p class='error'>Radera uppgift med post som inte finns returnerade {$svar->getStatus()} istället för förväntat 200</p>";
    }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel: {$ex->getMessage()}</p>";
    }
    return $retur;
}

/**
 * Test för funktionen validera formdata
 * @return string html-sträng med alla resultat för testerna
 */
function test_KontrolleraIndata(): string {
    $retur = "<h2>test_KontrolleraIndata</h2>";
    try{
    // Testa allt ok => 200
    $igar=new DateTimeImmutable("yesterday");
    $imorgon=new DateTimeImmutable("tomorrow");
    $postData=["date"=>$igar->format('Y-m-d'),
        "time"=>"05:00",
        "activityId"=>1,
        "description"=>"Hurra vad bra"];
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='error'>formdata med allt giltigt returnerade $svar</p>";
    } else {
        $retur .="<p class='ok'>formdata med allt giltigt returnerade ok</p>";
    }

    // Testa ogiltigt datum (Imorgon) => 400
    $postData["date"]=$imorgon->format("Y-m-d");
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med ogiltigt datum (Imorgon) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med ogiltigt datum (Imorgon) returnerade {[$svar]} ok förväntade fel</p>";
    }
    
    // Testa felaktigt datum format => 400
    $postData["date"]=$imorgon->format("d.m.Y");
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med ogiltigt datum format (d.m.Y) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med ogiltigt datum format (d.m.Y) returnerade {[$svar]} ok förväntade fel</p>";
    }
    

    // Testa datum saknas => 400
    unset($postData["date"]);
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med inget datum returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med inget datum returnerade {[$svar]} ok förväntade fel</p>";
    }

    // Testa felaktig tid (12h) => 400
    $postData["date"]=$igar->format('Y-m-d');
    $postData["time"]=("12:00");
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med felaktig tid (12h) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med felaktig tid (12h) returnerade {[$svar]} ok förväntade fel</p>";
    }
    
    // Testa felaktigt tidsformat => 400
    $postData["time"]=("5_30");
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med felaktigt tidsformat (5_30) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med felaktigt tidsformat (5_30) returnerade {[$svar]} ok förväntade fel</p>";
    }

    // Testa tid saknas => 400
    unset($postData["time"]);
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med ingen tid returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med ingen tid returnerade {[$svar]} ok förväntade fel</p>";
    }


    // Testa description saknas => 200
    $postData["time"]=("08:00");
    unset($postData["description"]);
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='error'>formdata med ingen description returnerade {[$svar]} förväntade okej</p>";
    } else {
        $retur .="<p class='ok'>formdata med ingen description returnerade okej som förväntat</p>";
    }

    // Testa aktivitetsId felaktigt (-1) => 400
    $postData["activityId"]=(-1);
    $postData["description"]=("hejsan");
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med aktivitetsId felaktigt (-1) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med aktivitetsId felaktigt (-1) returnerade {[$svar]} ok förväntade fel</p>";
    }

    // Testa aktivitetsId som saknas 100 => 400
    $postData["activityId"]=(100);
    $svar= validateindata($postData);
    if ($svar!==""){
        $retur .="<p class='ok'>formdata med aktivitetsId som saknas (100) returnerade fel som förväntat</p>";
    } else {
        $retur .="<p class='error'>formdata med aktivitetsId som saknas (100) returnerade {[$svar]} ok förväntade fel</p>";
    }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel: {$ex->getMessage()}</p>";
    }
    return $retur;
}
