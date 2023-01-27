<?php

declare (strict_types=1);

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter 
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum(new DateTimeImmutable($route->getParams()[0]), new DateTimeImmutable($route->getParams()[1]));
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift((int) $route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return sparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift((int) $route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift((int) $route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param int $sida
 * @return Response
 */
function hamtaSida(int $sida): Response {
    $posterPerSida=3;
    // Kolla att ID är ok
    $kollatSidnr= filter_var($sida, FILTER_VALIDATE_INT);   
    if (!$kollatSidnr || $kollatSidnr < 1) {
        $out=new stdClass();
        $out->error=["Felaktig sidnummer $sida angivet", "Läsningen misslyckades"];
        return new Response($out, 400);
    }

    // Koppla databasen
    $db=connectDb();

    // Hämta antal poster
    $result=$db->query("SELECT COUNT(*) FROM tasks");
    if($row=$result->fetch()) {
        $antalposter=$row[0];
    }
    $antalSidor=ceil($antalposter/$posterPerSida);

    // Hämta aktuella poster
    $first=($kollatSidnr-1)*$posterPerSida;
    $result=$db->query("SELECT t.ID, categoryID, Time, Date, Description, Category
                FROM tasks t
                INNER JOIN categories c on categoryID=c.id
                ORDER BY date asc
                LIMIT $first, $posterPerSida");

    // loop resultatsettet och skapa utdata
    $records=[];
    while($row=$result->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["categoryID"];
        $rec->activity=$row["Category"];
        $rec->time=substr($row["Time"], 0,5);
        $rec->date=$row["Date"];
        $rec->description=$row["Description"];
        $records[]=$rec;
    }

    // Returnera utdata
    $out=new stdClass();
    $out->page=$antalSidor;
    $out->tasks=$records;

    return new Response($out);
}

/**
 * Hämtar alla poster mellan angivna datum
 * @param DateTimeInterface $from
 * @param DateTimeInterface $tom
 * @return Response
 */
function hamtaDatum(DateTimeInterface $from, DateTimeInterface $tom): Response {
    // Kolla indata
    if($from->format("Y-m-d")>$tom->format("Y-m-d")) {
        $out= new stdClass;
        $out->error=["Felaktig indata", "Från-datum ska vara mindre än till-datum"];
        return new Response($out, 400);
    }
    // Koppla databas
    $db=connectDb();

    // Hämta poster
    $stmt=$db->prepare("SELECT t.ID, categoryID, Time, Date, Description, Category
            FROM tasks t
            INNER JOIN categories c on categoryID=c.id
            WHERE date between :from AND :to
            ORDER BY date asc");

    $stmt->execute(["from"=>$from->format("Y-m-d"), "to"=>$tom->format("Y-m-d")]);

    // Loopa resultatsettet och skapa utdata
    $records=[];
    while($row=$stmt->fetch()) {
        $rec=new stdClass();
        $rec->id=$row["ID"];
        $rec->activityId=$row["categoryID"];
        $rec->activity=$row["Category"];
        $rec->time=substr($row["Time"], 0,5);
        $rec->date=$row["Date"];
        $rec->description=$row["Description"];
        $records[]=$rec;
    }

    //returnera utdata
    $out= new stdClass;
    $out->tasks = $records;
    
    return new Response($out);
}

/**
 * Hämtar en enskild uppgiftspost
 * @param int $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(int $id): Response {
    return new Response("Hämta task $id", 200);
}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postData): Response {
    return new Response("Sparar ny task", 200);
}

/**
 * UppdateraUppgiftr en angiven uppgiftspost med ny information 
 * @param int $id id för posten som ska uppdateraUppgifts
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(int $id, array $postData): Response {
    return new Response("UppdateraUppgiftr task $id", 200);
}

/**
 * RaderaUppgiftr en uppgiftspost
 * @param int $id Id för posten som ska raderaUppgifts
 * @return Response
 */
function raderaUppgift(int $id): Response {
    return new Response("RaderaUppgiftr task $id", 200);
}
