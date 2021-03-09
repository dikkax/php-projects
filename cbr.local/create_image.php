<?php
session_start();
const W = 800, H = 300;

$i = imageCreate(W, H);
$white = imageColorAllocate($i, 0xFF, 0xFF, 0xFF);
$blue = imageColorAllocate($i, 0, 0, 255);
$black = imageColorAllocate($i, 0, 0, 0);

$sxml3 = simplexml_load_string(base64_decode($_SESSION['last_dyn_res']));

$count = 0; $max = 0; $min = trim($sxml3->ValuteCursDynamic->Vcurs);
foreach($sxml3->ValuteCursDynamic as $curs){
    $count++;
    if ($max < ($curs->Vcurs)) $max = trim($curs->Vcurs);
    if ($min > ($curs->Vcurs)) $min = trim($curs->Vcurs);
}
$min = $min - 1; $min = (int) $min;
$max = $max + 1; $max = (int) $max;
$dx = (W - 2)/ $count - 1; $dy = H / ($max - $min); $cnt = 0;
imageSetThickness($i, $dx);

foreach($sxml3->ValuteCursDynamic as $curs){
    $x = ($dx / 2) + $cnt * ($dx + 1) + 1;
    $y =  ($max - trim($curs->Vcurs)) * $dy;
    $ldate = trim($curs->CursDate);
    imageLine($i, $x, $y, $x, H, $blue);   
    if ($dx > 35) {
        imageString($i, 1, $x - ($dx / 2) , $y - 10, trim($curs->Vcurs), $black);
        imageString($i, 1, $x - ($dx / 2) , H - 10, substr($ldate, 8, 2) . "/" .substr($ldate, 5, 2) , $black);
    }
    $cnt++;
}

if ($dx <= 35){
    imageString($i, 3, 5, H - 15, $min, $black);
    imageString($i, 3, 5, 5, $max, $black);
}

header("Content-type: image/gif");
imageGif($i);
?>