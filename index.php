<?php

//Info base
$dbhost = "localhost";
$dbuser = "test";
$dbpass = "123";
$db = "mail";

//Chaine pour sauvegarder l'utilisateur après la connexion
$user="";


//Chaînes pour contenir le code html
$echoListe="";
$echoMessage="";
 
//Connexion base	
try
{
	$bdd = new PDO('mysql:host=localhost;dbname='.$db.';charset=utf8', $dbuser, $dbpass);
}
catch (Exception $e)
{
		die('Erreur : ' . $e->getMessage());
}


// Connexion d'un utilisateur	
if ((isset($_POST["mailConnexion"])) || (isset($_GET["user"]))){	
	if (isset($_POST["mailConnexion"])){
		$user = $_POST["mailConnexion"];
	}
	else{
		$user = $_GET["user"];
	}
	
	
//Envois d'un message	
	if ((isset($_POST["message"])) && (isset($_POST["dest"]))){
		$prep = $bdd->prepare('INSERT INTO donnee (destinataire,expediteur,date,message) VALUES (?,?,NOW(),?)');
		$prep->execute(array($_POST["dest"],$user,$_POST["message"]));
	}

	
//Affichage des mail dans le menu de gauche
	$req = "SELECT id, expediteur, message, date FROM donnee WHERE destinataire='".$user."'";
	$reponse = $bdd->query($req);
	while ($donnees = $reponse->fetch())
	{
		//Formatage du message en fonction de sa longueur pour afficher l'apercu dans le menu de gauche
		$point=""; 
		$apercu = substr($donnees['message'], 0, 10);
		if(strlen($donnees['message'])>10){
			$point="...";
		}
		//Insertion de l'HTML dans une chaîne
		$echoListe .= "<li>
							<a id=\"listeMail\" onclick=\"afficherMail(".$donnees['id'].",'".$user."')\" href=\"#\">
								".$donnees['date']." <b>".$donnees['expediteur']."</b> : ".$apercu."".$point."
							</a>
							<a id=\"croix\" onclick=\"supprimer(".$donnees['id'].",'".$user."')\" href=\"#\">
								x
							</a>
						</li>
						<br/>
						<hr width=`\"100%\">";
	}
	
	//Affichage des mails dans la zone de droite
	if (isset($_GET["id"])){
		$req = "SELECT message,expediteur, date FROM donnee WHERE id=".$_GET["id"]."";
		$reponse = $bdd->query($req);
		while ($donnees = $reponse->fetch())
		{
			$echoMessage = "<p>Le : ".$donnees['date']."<br/>De : <b>".$donnees['expediteur']."</b><br/>A : <b>".$user."</b><br/><br/>".$donnees['message']."</p>";
		}
	}
	
	//Suppresion d'un mail
	if (isset($_GET["idSUP"])){
		$prep = $bdd->prepare('DELETE FROM donnee WHERE id=?');
		$prep->execute(array($_GET["idSUP"]));
	}
}	

?>

<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
	</head>
	<body> 
			<form id="Connexion" <?php if($user!="")echo ("style=\"background-color:green\"")?>action="index.php" method="post">
				<input type="text" name="mailConnexion" maxlength="20"/>
				<input type="submit" value="Connexion">
			</form>
		
			<form id="envoi" action="index.php<?php if($user!="")echo("?user=".$user."")?>" method="post">
				<div id="divDest">
					<label for="dest">Destinataire:</label>
					<input type="text" id="dest" name="dest" style="width:100%" maxlength="20"/><br />
				</div>
				<div id="divMEssage">
					<label for="message">Message:</label>
					<input type="text" id="message" name="message"  style="width:100%" maxlength="300"/>
					<br/>
					<input type="submit" value="Envoyer" id="btnEnvoyer">
				</div>
			</form>
		
		

		<div id="gauche">
			<ul>
				<?php
					if($echoListe!="")echo $echoListe
				?>
			</ul>
		</div>
		<div id="droite">
			<?php
				if($echoMessage!="")echo $echoMessage
			?>
		</div>
	
		<script>
		
		//Afficher le mail lorsqu'il est cliqué dans le menu de gauche
			function afficherMail(id,user){
				xhr = new XMLHttpRequest();
				
				xhr.open('GET', 'http://localhost/index.php?user=' + user + '&id=' + id);
				xhr.send(null);
				xhr.onreadystatechange = function() {
					if (xhr.readyState == 4) {
						window.location.href="index.php?user=" + user + '&id=' + id;
					}
				}	
			}
			
		//Supprimer le mail quand la croix est cliquée
			function supprimer(id,user){
				xhr = new XMLHttpRequest();
				xhr.open('DELETE', 'http://localhost/index.php?user=' + user + '&idSUP=' + id);
				xhr.send(null);
					
				xhr.onreadystatechange = function() {
					if (xhr.readyState == 4) {
						window.location.href="index.php?user=" + user;
					}
				}
			}
		</script>
	</body>
</html>

