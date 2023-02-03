<?php

declare (strict_types=1);
require_once __DIR__ .'/funktioner.php';
/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function activities(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::GET) {
            return hamtaAllaAktiviteter();
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildAktivitet((int) $route->getParams()[0]);
        }
        if (isset($postData["activity"]) && count($route->getParams()) === 0 && 
            $route->getMethod() === RequestMethod::POST) {
                return sparaNyAktivitet((string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraAktivitet((int) $route->getParams()[0], (string) $postData["activity"]);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaAktivitet((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Returnerar alla aktiviteter som finns i databasen
 * @return Response
 */
function hamtaAllaAktiviteter(): Response {
    //Koppla mot databasen
    $db=connectDb();
   
    //Hämta alla poster från tabelen
    $resultat=$db->query("SELECT id, category from categories");

    //Lägga in posterna i en array
    $retur=[];
    while($row=$resultat->fetch()){
        $post=new stdClass();
        $post->id=$row['id'];
        $post->activity=$row['category'];
        $retur[]=$post;
    }
    $out= new stdClass;
    $out->activities=$retur;

    //Returnera svaret
    return new Response($out,200);
}

/**
 * Returnerar en enskild aktivitet som finns i databasen
 * @param int $id Id för aktiviteten
 * @return Response
 */
function hamtaEnskildAktivitet(int $id): Response {

    //kontrollera indata
    $kollaID= filter_var($id, FILTER_VALIDATE_INT);
    if (!$kollaID || $kollaID < 1) {
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out, 400 );
    }

    //koppla databas och hämta post
    $db= connectDb();
    $stmt=$db->prepare("SELECT id, category from categories where id=:id");
    if (!$stmt->execute(["id"=>$kollaID])) {
        $out=new stdclass();
        $out->error=["Fel vid läsning från databasen", implode(",", $stmt->errorInfo())];
        return new Response($out, 400 );
    }

    //sätt utdata och returnera utdata
    if($row=$stmt->fetch()){
        $out= new stdClass();
        $out->id=$row["id"];
        $out->activity=$row["category"];
        return new Response($out);
    } else {
        $out= new stdClass();
        $out->error=["Hittade ingen post med id=$kollaID"];
        return new response($out, 400);
    }

}

/**
 * Lagrar en ny aktivitet i databasen
 * @param string $aktivitet Aktivitet som ska sparas
 * @return Response
 */
function sparaNyAktivitet(string $aktivitet): Response {
    // kontrollera indata  
    $kontrolleradAktivitet=trim($aktivitet);
    $kontrolleradAktivitet= filter_var($kontrolleradAktivitet, FILTER_SANITIZE_SPECIAL_CHARS);
    if ($kontrolleradAktivitet==="") {
        $out=new stdClass();
        $out->error=["Fel vid spara", "Activity kan inte vara tom"];
        return new Response($out, 400);
    }
    try {
    // Koppla till databas
    $db=connectDb();
    // Spara till databasen
    $stmt=$db->prepare("INSERT INTO categories (category) VALUE (:category)");
    $stmt->execute(["category"=>$kontrolleradAktivitet]);
    $antalPoster=$stmt->rowCount();

    // Returnera svaret 
    if($antalPoster>0) {
        $out=new stdClass();
        $out->message=["Spara lyckades", "$antalPoster post(er) lades till"];
        $out->id=$db->lastInsertId();
        return new Response($out);
    } else {
        $out=new stdClass();
        $out->error=["något gick fel vid spara", implode(",", $db->errorInfo())];
        return new Response($out, 400);
    }
    } catch (Exception $ex) {
        $out = new stdClass();
        $out->error = ["Något gick fel vid spara", $ex->getMessage()];
        return new Response($out, 400);
    }
}

/**
 * UppdateraAktivitetr angivet id med ny text
 * @param int $id Id för posten som ska uppdateraAktivitets
 * @param string $aktivitet Ny text
 * @return Response
 */

function uppdateraAktivitet(int $id, string $aktivitet): Response {
    // Kontrollera indata
        // Kollar ID
    $kollaID= filter_var($id, FILTER_VALIDATE_INT);   
    if (!$kollaID || $kollaID < 1) {
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out, 400 );
    }
        // Kollar Aktivitet
    $kontrolleradAktivitet=trim($aktivitet);
    $kontrolleradAktivitet= filter_var($kontrolleradAktivitet, FILTER_SANITIZE_SPECIAL_CHARS);
    if ($kontrolleradAktivitet==="") {
        $out=new stdClass();
        $out->error=["Fel vid spara", "Activity kan inte vara tom"];
        return new Response($out, 400);
        }
    try {
    // Koppla databas
        $db=connectDb();
    // UppdateraAktivitet post
        $stmt=$db->prepare("UPDATE categories SET category = :category WHERE id=:id");
        $stmt->execute(["category"=>$kontrolleradAktivitet, "id"=>$kollaID]);
        $antalPoster=$stmt->rowCount();
    
    // Returnera svar
    $out = new stdClass();
    if($antalPoster>0) {
        $out->result = true;
        $out->message=["Spara lyckades", "$antalPoster aktiviteter uppdaterades"];
    } else {
        $out->result = false;
        $out->message=["Spara lyckades", "0 aktiviteter uppdaterades"];
    }

    return new Response($out, 200);
} catch (Exception $ex) {
    $out = new stdClass();
    $out->error = ["Något gick fel vid uppdatering", $ex->getMessage()];
    return new Response($out, 400);
}
}

/**
 * RaderaAktivitetr en aktivitet med angivet id
 * @param int $id Id för posten som ska raderaAktivitets
 * @return Response
 */
function raderaAktivitet(int $id): Response {
    // kontrollera ID
    $kollaID= filter_var($id, FILTER_VALIDATE_INT);   
    if (!$kollaID || $kollaID < 1) {
        $out=new stdClass();
        $out->error=["Felaktig indata", "$id är inget heltal"];
        return new Response($out, 400 );
    }
    try {
        // Koppla mot databas
            $db=connectDb();
        
        // Skicka raderaAktivitet komando
        $stmt=$db->prepare("DELETE FROM categories WHERE ID=:id");
        $stmt->execute(["id"=>$kollaID]);
        $antalPoster=$stmt->rowCount();

        // Kontrollera databas-svar och skapa utdata-svar
        $out=new stdClass();
        if($antalPoster>0){
            $out->result=true;
            $out->message=["RaderaAktivitet lyckades", "$antalPoster post(er) raderaAktivitetdes"];
        } else {
            $out->result=false;
            $out->message=["RaderaAktivitet misslyckades", "inga poster raderaAktivitetdes"];
        }
        return new Response($out);
    } catch (Exception $ex) {
        $out = new stdClass();
        $out->error = ["Något gick fel vid raderaAktivitet", $ex->getMessage()];
        return new Response($out, 400);
    }
}
