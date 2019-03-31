<?php
//Zeilenumbruch mit *** markieren
$arr_text = explode("***",$_GET["text"]);
if(!$_GET["text"]){$arr_text = explode("***","Test Test Text***Test Text***Test Text");}
$Kriterium=$_GET["kriterium"];
//Hintergrundfarbe
$bgcolor = $_GET["bgcolor"] == 'dark' ? 'f5f5f5' : 'ffffff';
$array_Kriterien = array("",
	"selbständig arbeiten",
	"sorgfältig arbeiten",
	"sich aktiv am***Unterricht beteiligen",
	"eigene Fähigkeiten***einschätzen",
	"mit anderen***zusammenarbeiten",
	"konstruktiv mit***Kritik umgehen",
	"respektvoll mit anderen***umgehen",
	"Regeln einhalten"
);
$arr_text = explode("***",$array_Kriterien[$_GET["kriterium"]]);








//*Einstellungen vornehmen*//
$font_size = 10;
$font_file = "OpenSans-Regular.ttf";
//$font_file = "OpenSans-Semibold.ttf";

$font_color = "006699"; //Doppelkreuz # weglassen
$font_color = "337ab7";
//$font_color = "000000";
//$font_color = "ff0000";


$paddingY = 1; //Abstand oben unten
$paddingX = 10; //Abstand li & re
$lineheight = 1.5; //Zeilenabstand einstellen
$angle = 0;
$cache_folder = "cache";
$cache_images = FALSE;

/*
  ---------------------------------------------------------------------------
   For basic usage, you should not need to edit anything below this comment.
   If you need to further customize this script's abilities, make sure you
   are familiar with PHP and its image handling capabilities.
  ---------------------------------------------------------------------------
*/

$mime_type = 'image/png' ;
$extension = '.png' ;
$send_buffer_size = 4096 ;

//Wenn das Bild bereits im cache vorhanden ist, wird das Bild aus dem cache Ordner geladen.
if($cache_images === TRUE){
	// look for cached copy, send if it exists
	$hash = md5(basename($font_file) . $font_size . $font_color . $bgcolor . implode("***",$arr_text)) ;
	$cache_filename = $cache_folder . '/' . $hash . $extension ;
	if($cache_images && (is_file($cache_filename)) && (is_readable($cache_filename)))
	{
		$file=fopen($cache_filename,'r');
		header('Content-type: ' . $mime_type) ;
		while(!feof($file))
			print(($buffer = fread($file,$send_buffer_size))) ;
		fclose($file) ;
		die();
	}
}

//Anzahl Zeilen bestimmen
$Anz_Zeilen = count($arr_text);

//Bildbreite 
$arr_laengsteZeile = array();
foreach($arr_text as $Zeile){
	$box = imagettfbbox ($font_size, $angle , $font_file , trim($Zeile));
	$BreiteBox = $box[2] - $box[0];
	array_push($arr_laengsteZeile,$BreiteBox);
}
rsort($arr_laengsteZeile);
$imw = $arr_laengsteZeile[0] + 2*$paddingX;

//Bildhöhe
$imh = 2*$paddingY + ($Anz_Zeilen*$font_size*$lineheight);

// Bild erzeugen und temporär speichern
$image = imagecreate($imw, $imh);

// Hintergrundfarbe definieren (RGB)
$bgcolor = hex2dec($bgcolor);
imagecolorallocate($image, $bgcolor["r"], $bgcolor["g"], $bgcolor["b"]);

//Fals Hintergrund transparent sein soll... verträgt sich jedoch nicht mit imagerotate()
//imagecolortransparent($image, $bgcolor);

//Textfarbe definieren (RGB)
$font_color=hex2dec($font_color);
$font_color = imagecolorallocate($image, $font_color["r"], $font_color["g"], $font_color["b"]);

//Zeilen in das Bild schreiben
$i=0;
foreach($arr_text as $Zeile){
	$yPos = $font_size + $paddingY + ($i*$lineheight*$font_size);
	$xPos = $paddingX;
	imagettftext($image,$font_size,0,$xPos,$yPos,$font_color,$font_file,trim($Zeile)) ;
	$i++;
}

//Bild drehen
$image = imagerotate($image,90,1);

//Ausgabe
// Dem Browser mitteilen, dass nun ein Bild kommt
header('Content-type: ' . $mime_type) ;
imagepng($image);

//Falls erwünscht Bild im cache Ordner speichern
if($cache_images === TRUE)
{
    imagepng($image,$cache_filename) ;
}
imagedestroy($image);







//functions
function hex2dec($bgcolor){
	$r = hexdec(substr($bgcolor, 0, 2));
	$g = hexdec(substr($bgcolor, 2, 2));
	$b = hexdec(substr($bgcolor, 4, 2));
	$color = array ('r' => $r, 'g' => $g,'b' => $b);
	return $color;
}
?>