<?
error_reporting(E_ALL);
ini_set("display_errors", 1);
echo ("<p>Hallo erst mal</p>");
// Create DOM from URL or file
$doc = new DOMDocument();
$doc->strictErrorChecking = FALSE;
$doc->loadHTMLFile("Peter Bartels.htm");
$xml = simplexml_import_dom($doc);
// Find all images
foreach($xml->body[0] as $element){
       echo (string) $element . 'hier id<br>';
}
// Find all links
foreach($xml->body[0]->a as $element){
       echo $element->href . 'hier a<br>'; 
}
//http://www.php.net//manual/de/simplexml.examples-basic.php
/*
$xml = simplexml_load_file("Peter Bartels.htm"); 
echo $xml;
// foreach ($xml->movie as $movie) 
foreach ($xml->h1 as $h) {
	echo '<p>Hier bin ich'. (string) $h['class']. "</p>\n";
}
*/
echo ("<p>Hallo erst mal</p>");

?>