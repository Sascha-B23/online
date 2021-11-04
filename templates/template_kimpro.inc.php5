<?php
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    //curl_setopt($ch, CURLOPT_URL, 'https://gorest.co.in/public/v1/users');
    curl_setopt($ch, CURLOPT_URL, 'http://172.28.0.1:8010/classifications/?skip=0&limit=100');
    //curl_setopt($ch, CURLOPT_PROXY, $_SERVER['SERVER_ADDR'] . ':' .  $_SERVER['SERVER_PORT']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $content = curl_exec($ch);
    $httpReturnCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if ($content === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('error in curl exec! ' . $info . $httpReturnCode);
    }
    
    // ***
    $result = json_decode($content);
?>
<head>
<link href="https://fonts.googleapis.com/css?family=Montserrat&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Ubuntu&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Inter&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Poppins&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet" />
<link href="..\..\css\kimpro_main.css" rel="stylesheet" />
</head>
<div style="width: 1024px;" id="div_body_main_content">
<link href="..\..\css\kimpro_main.css" rel="stylesheet" />
<table class="table">
<thead class="table-header">
    <tr>
        <th scope="col">Dokumentenname</th>
        <th scope="col">Dokumententyp (Konfidenz)</th>
        <th scope="col">Jahr (Konfidenz)</th>
        <th scope="col">Standort (Konfidenz)</th>
        <th scope="col">Übernahme</th>
    </tr>
</thead>
<tbody class="table-content">
<?php foreach($result as $key=>$value): ?>
    <tr class="table-row" id=<?= $key; ?>>
        <td><a href="<?=$DOMAIN_HTTP_ROOT?>de/kimpro/pdfviewer.php5?UID=<?=$UID?>&file=<?=$value->document_name;?>"><?= $value->document_name; ?><a></td>
        <!-- PHP implementierung nötig zur dynamischen Erzeugung des td uebernahme_click_ je nach Dokumententyp! (weiterleitung zu NKAS oder Vertragsdatenerfaasung) -->
        <?php
            // Dokumentenname als Post-variable mitgeben, damit die Datei auf dem Server abgespreichert werden kann
            $doctype;
            $createLink;
            $_SESSION["filename".$value->document_location_id] = $value->document_name;
            if ($value->document_type == 0) {
                $doctype = "Teilabrechnung";
                $createLink= $DOMAIN_HTTP_ROOT."de/administration/teilabrechnung_edit_classified.php5?UID=".$UID."&location=".$value->document_location_id;
            } elseif ($value->document_type == 1) {
                $doctype = "Mietvertrag";
                $createLink= $DOMAIN_HTTP_ROOT."de/administration/contract_edit_classified.php5?UID=".$UID."&location=".$value->document_location_id;
            } else {
                $doctype = "Antwortschreiben";
                $createLink= $DOMAIN_HTTP_ROOT."de/administration/contract_edit_classified.php5?UID=".$UID."&location=".$value->document_location_id;
            }
        ?>
        <td><?=$doctype;?> (<?=round($value->document_type_confidence, 2); ?>)</td>
        <td><?=$value->document_year; ?> (<?=round($value->document_year_confidence, 3); ?>)</td>
        <td><?=$value->document_location; ?> (<?=round($value->document_location_confidence, 3); ?>)</td>
        <td id="uebernahme_click"><a href=<?=$createLink;?>>Ja</a><br><a onclick="deleteListElement(<?=$key;?>)">Nein</a></td>
        <!-- Javascript Implementierung notwendig mit dem entsprechenden API-Call, der das Dokument dann löscht -->
    </tr>
    <?php endforeach; ?>
</tbody>
</table>
<div class="upload-field">
    <div>
        <span class="upload-span">Upload Dokumente </span>
        <form method="post" enctype="multipart/form-data">
             <label class="upload-span2">
                <input name="datei" type="file" size="50" accept="text/*" value="C:\Users\companyASP\Downloads\NK-Mode von Feucht-Wörgl, Salzburger Str. 32 (M4 Wörgl)-14.05.14-1948.pdf"> 
            </label>  
        <button style="margin-top: 135px; margin-left: 20px;">hochladen</button>
        </form>
    </div>
</div>
</div>
<script>
    function deleteListElement(key){
        var el  = document.getElementById(key);
        el.remove();
    }
</script>


