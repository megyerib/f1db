<?php
/* NYELVVÁLASZTÓ

$lngs tömbben frissíteni kell a nyelveket
A gyorsaság miatt nem olvassa ki a létező nyelveket a könyvtárból

Megj.: a setcookie parancsoknál be kell állítani az útvonalat (gyökér "/")
       hogy az egyes almenüpontokban ne állítódjon be külön-külön a nyelv
*/
$lngs = array(
	'en' => 'English',
	'hu' => 'Magyar'
);

if (isset($_GET['lang']) && isset($lngs[$_GET['lang']])) {
	$nyelv = $_GET['lang'];
	setcookie('lang', $nyelv, time() + 2592000, "/"); // Süti beállítása 1 hónapra
	$_SESSION['lang'] = $nyelv;
	header('Location: '.$_GET['url']);
}
else if (isset($_COOKIE['lang']) /*|| isset($_SESSION['lang'])*/) { // Van adat a nyelvre
	if (isset($_COOKIE['lang'])) {
		$nyelv = $_COOKIE['lang'];
		$_SESSION['lang'] = $nyelv;
	}
	else {
		$nyelv = $_SESSION['lang'];
	}
}
else { // Nincs adat
	$browser_lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
	$nyelv = $browser_lang;
	setcookie('lang', $nyelv, time() + 2592000, "/"); // Süti beállítása 1 hónapra
	$_SESSION['lang'] = $nyelv;
}

// TÖMB BETÖLTÉSE
if ($nyelv != 'en') { // Nem angol
	if (!isset($lngs[$nyelv])) {
		$nyelv = 'en';
	}
} else { // Angol
	$nyelv = 'en';
}
$eng = parse_ini_file('lang/en.ini'); // Angol tömb betöltése (minden megvan benne)
$lang = parse_ini_file('lang/'.$nyelv.'.ini');
$lang = array_replace($eng, $lang); // Felülírás
?>