<!doctype html>
<html lang="de">
  <head>
    <meta charset="utf-8">
    <title>Personencheck</title>
  </head>
<body>
<?
function getid1($link){
	// Beispiel kp0051.php?__kpenr=451&grnr=0
	// gibt 451 zurueck
	return substr($link,strpos($link,"=")+1,(strpos($link,"&")-strpos($link,"=")-1));
}
function checkid1($id,$personenfile){
	$xml = new DomDocument();
	$xml->load($personenfile);
    $xpath = new DomXPath($xml);
	$erg=$xpath->query("//*[@id='$id']")->item(0);
	if ($erg){
		$nodes = $erg->childNodes;
		foreach ($nodes as $node) {
			//echo "NodeName'".$node->nodeName."'\n";
			if ($node->nodeName=="name"){
				return $node->nodeValue;
			}
    }
		return $erg;
	}	
}

function addperson1($data,$xmlfile){
	$dom = new DOMDocument('1.0', 'utf-8');
	$dom ->load($xmlfile);
	$insite = $dom->getElementsbyTagName('all')->item(0);
	//erzeuge Person
	$personNode = $dom->createElement("person");
	$personNode->setAttribute("id",$data['id']);
	$personNode->appendChild($name=$dom->createElement("name",$data['name']));
	//$name->setAttribute("type","single");
	$personNode->appendChild($givenName=$dom->createElement("givenName",$data['givenName']));
	$personNode->appendChild($familyName=$dom->createElement("familyName",$data['familyName']));
	$personNode->appendChild($familyName=$dom->createElement("gender",$data['gender']));
	if (!empty($data['title'])){
		$personNode->appendChild($title=$dom->createElement("title",$data['title']));
	}
	//hier Ende
	$insite->appendChild($personNode);
	$dom->save($xmlfile);
}
function getvalues($id,$xmlfile){
	$xml = new DOMDocument();
	$xml ->load($xmlfile);
	$xpath = new DomXPath($xml);
	$erg=$xpath->query("//*[@id='$id']")->item(0);
	if($erg){
		$person = $erg->childNodes;
		foreach ($person as $child){
			$data[$child->nodeName]=$child->nodeValue;
		}
	}
	else {
		echo "Kein Ergebnis";
	}
	return $data;
}
function modify($data,$xmlfile){
	$dom = new DOMDocument('1.0', 'utf-8');
	$dom->validateOnParse = true;
	$dom ->Load($xmlfile);
	echo "Id ist".$data['id'];
	//$erg = $dom->getElementsByTagName('person');
	$n=0;
	//Datensatz löschen
	/*foreach ($dom->getElementsByTagName('person') as $person){
		if ($person->hasAttribute ('id')) {
			if ($person->getAttribute ('id')==$data['id']){
			   $erg=$person->parentNode->removeChild($person);	
			}
		}
		$n++;
	}*/
	foreach ($dom->getElementsByTagName('person') as $person){
		if ($person->hasAttribute ('id')) {
			if ($person->getAttribute ('id')==$data['id']){
			   foreach($person->childNodes as $child){
					if ($data[$child->nodeName]!=$child->nodeValue){
				      echo "Hier nodeName ".$child->nodeName;
					  /*
					  $newchild=$dom->createElement($child->nodeName,$data[$child->nodeName]);
					  $erg=$child->parentNode->replaceChild($newchild, $child); 
					  */
					  $newchild=$dom->createElement($child->nodeName,$data[$child->nodeName]);
					  $child->parentNode->replaceChild($newchild, $child); 
					}
			   }
			}
		}
		$n++;
	}
	$dom->save($xmlfile);
	
}

$personensite="./data/kp0041.htm";
$personenfile="./data/person.xml";
$html = file_get_contents($personensite);
$doc = new DOMDocument();
$doc->loadHTML($html);
$sxml = simplexml_import_dom($doc);
ini_set('display_errors', 1);
error_reporting(E_ALL);
// get body
$tbody=$sxml->xpath("//*[@id='smc_page_kp0041_contenttable1']/tbody");
//Hole Name
if (isset($_POST['last'])){
	$last=$_POST['last'];
	$methode=$_POST['type'];
	$now=++$last;

	switch ($methode) {
	case "modyfy":
		$data['id']=$_POST['id'];
		$data['name']=$_POST['name'];
		$data['title']=$_POST['title'];
		$data['givenName']=$_POST['givenName'];
		$data['familyName']=$_POST['familyName'];
		$data['gender']=$_POST['gender'];
		modify($data,$personenfile);
		break;
	case "weiter":
		echo "Es geht weiter";
		break;
	case "new":
		$data['id']=$_POST['id'];
		$data['name']=$_POST['name'];
		$data['title']=$_POST['title'];
		$data['givenName']=$_POST['givenName'];
		$data['familyName']=$_POST['familyName'];
		$data['gender']=$_POST['gender'];
		addperson1($data,$personenfile);
		break;
	}
}
else{
	$last=-1;
	$now=++$last;

}
//Name wird html Datei ausgelesen
$namehtml=$tbody[0]->tr[$now]->td[0]->a[0];
echo "Name ist $namehtml<br />\n";
$namesplit=explode(" ", $namehtml);
//pruefe,ob sich Name geaendert hat

if ($last<$tbody[0]->count()){

	echo "now ist $now<br />\n";
	
	//foreach($tbody[0]->tr as $tr){
		//Es wird nur td[0] benoetigt
		$link = (string) $namehtml['href'];
		$id=getid1($link);
		echo "id ist '$id'<br />\n";
		$namecheck=checkid1($id,$personenfile);	
		if ($namecheck){
			$values=getvalues($id,$personenfile);
			echo "$id existiert bereits";
			echo "given Name".$values['givenName']."</br>";
			if (!isset($values['title'])){
				$values['title']="";
			}
			$exist=True;
		}
		else{
			$values['givenName'] =	$namesplit[0];
			$values['familyName'] = $namesplit[1];
			$values['title']="";
			$values['gender']="Male";
			$exist=False;

		}
			echo'
			<form action="personcheck.php" method="post">'."\n".'
			    <input type="hidden" name="id" value="'.$id.'">'."\n".'
				<input type="hidden" name="last" value="'.$now.'">'."<br \>\n".'
				<input type="hidden" name="name" value="'.$namehtml.'">'."<br \>\n".'
				<input type="hidden" name="type" value="add">'."<br \>\n".'
				title <input type="text" name="title" value="'.$values['title'].'">'."<br \>\n".'
				givenName <input type="text" name="givenName" value="'.$values['givenName'].'">'."<br \>\n".'
				familyName <input type="text" name="familyName" value="'.$values['familyName'].'">'."<br \>\n";
				if ($values['gender']=="Female"){
					echo
					'<input type="radio" name="gender" value="Male"> Male<br>
					<input type="radio" name="gender" value="Female" checked> Female<br>';
				} else {
					echo
					'<input type="radio" name="gender" value="Male" checked> Male<br>
					<input type="radio" name="gender" value="Female"> Female<br>';
				}
				if($exist){
					echo'<input type="submit" name="type" value="modyfy">'."<br \>\n";
				}
				else{
					echo'<input type="submit" name="type" value="new">'."<br \>\n";
				}
				echo'
				<input type="submit" name="type" value="weiter">'."<br \>\n".'
			</form>
			';

	echo "</p>";
}
//var_dump($tbody[0]->tr[0]);

echo "Hallo Welt";
?>
</body>
</html>