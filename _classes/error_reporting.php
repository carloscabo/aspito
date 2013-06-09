<?php
// - LA IDEA ES QUE SI ESTÁ EN LOCALHOST MUESTRE LOS ERRORES PERO ONLINE NO.
error_reporting (0);

//SI EL SUBDOMINIO EMPIEZA POR lh (de 'LOCALHOST').
$subdominio = array_shift(explode(".",$_SERVER['HTTP_HOST']));
if ($subdominio == 'lh') {
	require $_SERVER['DOCUMENT_ROOT'] . '/_classes/debuglib.php';
	//ENABLE ALL ERRORS
	error_reporting(-1);
	print_a('DEBUG');
}

?>