<?php

function menuGeneralas(string $aktualisOldal) {
    session_status() === PHP_SESSION_ACTIVE || session_start();
    echo "<nav><div class='menu'><ul>" .
        "<li" . ($aktualisOldal === "index" ? ' class=active' : "") . ">" .
        "<a href='index.php'>Kezdőlap</a>" .
        "</li>" .
        "<li" . ($aktualisOldal === "jegyfoglalas" ? ' class=active' : "") . ">" .
        "<a href='jegyfoglalas.php'>Jegyfoglalás</a>" .
        "</li>" .
        "<li" . ($aktualisOldal === "profil" ? ' class=active' : "") . ">" .
        (isset($_SESSION['user']) === true ? '<li><a href="profil.php">Profil</a></li>' : '<li><a href="bejelentkezes.php">Bejelentkezés/Regisztráció</a></li>') .
        "</li>" .
        (isset($_SESSION['user']) === true ? '<li><a href="logout.php">Kijelentkezés</a></li>' : '') .
        (isset($_SESSION['user']) === true && $_SESSION['user']['felhasznalonev'] === 'admin' ? "<li" . ($aktualisOldal === "admin" ? ' class=active' : "") . '><a href="admin.php">Admin</a></li>' : '') .
        "</ul></div></nav>";
}

function kiindulasiHelyListazas(int $melyik) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    if($melyik === 1) {
        $honnan1 = oci_parse($utazasiiroda, "SELECT DISTINCT(HONNAN) FROM JARAT");
        oci_execute($honnan1);

        while ($current_row = oci_fetch_array($honnan1, OCI_ASSOC + OCI_RETURN_NULLS)) {
            echo '<option value="'. $current_row["HONNAN"] . '"' . '>' . $current_row["HONNAN"] . '</option>';
        }
    }

    if(isset($honnan1) && is_resource($honnan1)) {
        oci_free_statement($honnan1);
    }

    csatlakozas_zarasa($utazasiiroda);
}

function erkezesiHelyListazas(int $melyik) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    if($melyik === 1) {
        $hova1 = oci_parse($utazasiiroda, "SELECT DISTINCT(HOVA) FROM JARAT");
        oci_execute($hova1);

        while ($current_row = oci_fetch_array($hova1, OCI_ASSOC + OCI_RETURN_NULLS)) {
            echo '<option value="'. $current_row["HOVA"] . '"' . '>' . $current_row["HOVA"] . '</option>';
        }
    }

    if(isset($hova1) && is_resource($hova1)) {
        oci_free_statement($hova1);
    }

    csatlakozas_zarasa($utazasiiroda);
}

function legitarsasagListazas() {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $legitarsasag = oci_parse($utazasiiroda, "SELECT NEVE FROM LEGITARSASAG");
    oci_execute($legitarsasag);

    while ($current_row = oci_fetch_array($legitarsasag, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo '<option value="'. $current_row["NEVE"] . '"' . '>' . $current_row["NEVE"] . '</option>';
    }

    if(isset($legitarsasag) && is_resource($legitarsasag)) {
        oci_free_statement($legitarsasag);
    }

    csatlakozas_zarasa($utazasiiroda);
}

function ertekel($felhasznalonev, $legitarsasag_id, $ertekeles) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $sysdate = date('Y-m-d H:i:s');
    $ertekeles = mysqli_query($utazasiiroda, "INSERT INTO ERTEKEL VALUES (NULL, '$felhasznalonev', '$legitarsasag_id', '$ertekeles', '$sysdate')") or die ('Hibás utasítás!');
    header("Location: profil.php?ertekeles=true");

    if(isset($ertekeles) && is_resource($ertekeles)) {
        oci_free_statement($ertekeles);
    }

    csatlakozas_zarasa($utazasiiroda);
}

function kereses($kiindulasiHely, $erkezesiHely, $datum, $legitarsasag, $melyik) {
    if($melyik === 'egy') {
        $kiindulasiHely = empty($kiindulasiHely) === true ? '%' : $kiindulasiHely;
        $erkezesiHely = empty($erkezesiHely) === true ? '%' : $erkezesiHely;
        $datum = empty($datum) === true ? '%' : $datum;
        $legitarsasag = empty($legitarsasag) === true ? '%' : $legitarsasag;

        $egyiranyu_kereses = "SELECT JARATSZAM, HONNAN, HOVA, TO_CHAR(INDULAS,'YYYY.MM.DD. HH:MI') AS INDULAS, TO_CHAR(ERKEZES,'YYYY.MM.DD. HH:MI') AS ERKEZES, LEGITARSASAG.NEVE, TOBBMEGALLOS, SZABAD_HELY FROM JARAT, LEGITARSASAG 
                                        WHERE JARAT.LEGITARSASAG=LEGITARSASAG.NEVE AND TOBBMEGALLOS=0 AND SZABAD_HELY > 0 AND HONNAN LIKE '$kiindulasiHely' 
                                        AND HOVA LIKE '$erkezesiHely' AND INDULAS LIKE '$datum' AND LEGITARSASAG.NEVE LIKE '$legitarsasag'";

        return $egyiranyu_kereses;
    } else if ($melyik === 'tobb') {
        $kiindulasiHely = empty($kiindulasiHely) === true ? '%' : $kiindulasiHely;
        $erkezesiHely = empty($erkezesiHely) === true ? '%' : $erkezesiHely;
        $datum = empty($datum) === true ? '%' : $datum;
        $legitarsasag = empty($legitarsasag) === true ? '%' : $legitarsasag;

        $tobbmegallos_kereses = "SELECT JARATSZAM, HONNAN, HOVA, TO_CHAR(INDULAS,'YYYY.MM.DD. HH:MI') AS INDULAS, TO_CHAR(ERKEZES,'YYYY.MM.DD. HH:MI') AS ERKEZES, LEGITARSASAG.NEVE, TOBBMEGALLOS FROM JARAT, LEGITARSASAG 
                                        WHERE JARAT.LEGITARSASAG=LEGITARSASAG.NEVE AND TOBBMEGALLOS NOT LIKE 0 AND SZABAD_HELY > 0 AND HONNAN LIKE '$kiindulasiHely' 
                                        AND HOVA LIKE '$erkezesiHely' AND INDULAS LIKE '$datum' AND LEGITARSASAG.NEVE LIKE '$legitarsasag'";

        return $tobbmegallos_kereses;
    }
}

function utasAdatok($tipus, $utas_szam) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    for($i = 1; $i <= $utas_szam; ++$i) {
        echo '<fieldset>';
        echo '<legend>' . $i . ". Utas adatai ($tipus)" . '</legend>';
        echo "<select required>";
        echo '<option disabled selected value>--Nem</option>';
        echo '<option>Férfi</option>';
        echo '<option>Nő</option>';
        echo '</select> <br/>';
        echo '<label class="required-label">Vezetéknév:<input type="text" placeholder="Vezetéknév" required></label>';
        echo '<label class="required-label">Keresztnév:</label><input type="text" placeholder="Keresztnév" required> <br/>';
        echo '<label class="required-label">Születési dátum:</label><input type="date" required>';

//        echo '<table class="biztositas-tabla">';
//        echo '<caption>Biztosítás a teljes útra</caption>';
//        $poggyasz = mysqli_query($utazasiiroda, "SELECT * FROM POGGYASZ") or die ("Hibás utasítás!");
//        while(($current_row = mysqli_fetch_assoc($poggyasz))!= null) {
//            echo '<tr>';
//            echo '<th>';
//            echo '<label><input type="radio"' . " value='" . $current_row["ID"] . "'" . " name='" . "poggyasz-reszletek-" . $tipus. '-' . $i . "'" . ($current_row["ID"] == "1" ? ' checked' : '') . ">" .
//                $current_row["MEGNEVEZES"] . '</label>';
//            echo '</th>';
//            echo '<td>' . '+ ' . number_format($current_row['AR']) . ' Ft' . '</td>';
//            echo '</tr>';
//        }

        echo '<tr>';
        echo '<th>';
        echo '<label><input type="checkbox" name="etkezes-' . $tipus. '-' . $i . '" value="etkezes" checked>Étkezés</label>';
        echo '</th>';
        echo '<td>+ 5,720 Ft</td>';
        echo '</tr>';
        echo '</table>';
        echo '</fieldset>';
    }

    csatlakozas_zarasa($utazasiiroda);
}

function repulojegyAra($jaratszam): int {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $repjegy_ara='0';               // az ár kezdőértéke 0
    $repjegy_ar_lekerdezes = oci_parse($utazasiiroda, "SELECT AR FROM JARAT WHERE JARATSZAM = '$jaratszam'") or die ('Hibás utasítás!');
    oci_execute($repjegy_ar_lekerdezes);
    while($current_row = oci_fetch_array($repjegy_ar_lekerdezes, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $repjegy_ara = $current_row["AR"];
    }

    oci_free_statement($repjegy_ar_lekerdezes);
    csatlakozas_zarasa($utazasiiroda);
    return $repjegy_ara;
}

function poggyaszAra($poggyaszid): int {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $poggyasz_megnevezes='';
    $poggyasz_ara='0';
    $poggyasz_lekerdezes = oci_parse($utazasiiroda, "SELECT MEGNEVEZES, AR FROM POGGYASZ WHERE ID = '$poggyaszid'") or die ('Hibás utasítás!');
    oci_execute($poggyasz_lekerdezes);
    while($current_row = oci_fetch_array($poggyasz_lekerdezes, OCI_ASSOC + OCI_RETURN_NULLS)) {
        $poggyasz_megnevezes =  $current_row['MEGNEVEZES'];
        $poggyasz_ara = $current_row["AR"];
    }
    echo '<tr>';
        echo '<th>' . $poggyasz_megnevezes . '</th>';
        echo '<td>1 x ' . number_format($poggyasz_ara) . ' Ft</td>';
    echo '</tr>';

    if(isset($poggyasz_lekerdezes) && is_resource($poggyasz_lekerdezes)) {
        oci_free_statement($poggyasz_lekerdezes);
    }

    csatlakozas_zarasa($utazasiiroda);
    return $poggyasz_ara;
}

function profilkepFeltoltese(array &$errors, string $felhasznalonev) {
    if (isset($_FILES["profil-kep"]) && is_uploaded_file($_FILES["profil-kep"]["tmp_name"])) {

        if ($_FILES["profil-kep"]["error"] !== 0) {
            $errors[] = "Hiba történt a fájlfeltöltés során!";
        }

        $engedelyezettKiterjesztesek = ["png", "jpg"];

        $kiterjesztes = strtolower(pathinfo($_FILES["profil-kep"]["name"], PATHINFO_EXTENSION));

        if (!in_array($kiterjesztes, $engedelyezettKiterjesztesek)) {
            $errors[] = "A profilkép kiterjesztése hibás! Engedélyezett formátumok: " .
                implode(", ", $engedelyezettKiterjesztesek) . "!";
        }

        if ($_FILES["profil-kep"]["size"] > 5242880) {
            $errors[] = "A fájl mérete túl nagy!";
        }

        if (count($errors) === 0) {
            $utvonal = "img/profil-kepek/$felhasznalonev.$kiterjesztes";
            $flag = move_uploaded_file($_FILES["profil-kep"]["tmp_name"], $utvonal);


            if (!$flag) {
                $errors[] = "A profilkép elmentése nem sikerült!";
            }
        }
    }
}

function foglalasokListazasa($felhasznalonev) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $foglalt = oci_parse($utazasiiroda, "SELECT JARAT.HONNAN, JARAT.HOVA, TO_CHAR(JARAT.INDULAS,'YYYY.MM.DD. HH:MI') AS INDULAS, JEGY.AR FROM JEGY, JARAT WHERE JEGY.FELHASZNALONEV = '$felhasznalonev' AND JEGY.JARATSZAM=JARAT.JARATSZAM") or die ('Hibás utasítás!');
    oci_execute($foglalt);
    while ($current_row = oci_fetch_array($foglalt, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo '<tr>';
        echo '<td>' . $current_row['HONNAN'] . '</td>';
        echo '<td>' . $current_row['HOVA'] . '</td>';
        echo '<td>' . $current_row['INDULAS'] . '</td>';
        echo '<td>' . number_format($current_row['AR']) . ' Ft' .  '</td>';
        echo '</tr>';
    }

//    if(isset($foglalas) && is_resource($foglalas)) {
//        mysqli_free_result($foglalas);
//    }

    csatlakozas_zarasa($utazasiiroda);
}

function ertekelesekListazasa($felhasznalonev) {
    include_once('common/connection.php');
    $utazasiiroda = csatlakozas();

    $ertekeles = oci_parse($utazasiiroda, "SELECT LEGITARSASAG.NEVE, ERTEKELES FROM LEGITARSASAG, ERTEKEL WHERE ERTEKEL.FELHASZNALONEV = '$felhasznalonev' AND LEGITARSASAG.NEVE=ERTEKEL.LEGITARSASAG");
    oci_execute($ertekeles);
    while ($current_row = oci_fetch_array($ertekeles, OCI_ASSOC + OCI_RETURN_NULLS)) {
        echo '<tr>';
        echo '<td>' . $current_row['NEVE'] . '</td>';
        echo '<td>' . $current_row['ERTEKELES'] . '</td>';
        echo '</tr>';
    }

//    if(isset($ertekeles) && is_resource($ertekeles)) {
//        mysqli_free_result($ertekeles);
//    }

    csatlakozas_zarasa($utazasiiroda);
}
