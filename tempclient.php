<?php

// This quick command line script tests tempserver API.

// SYNTAX: tempclient.php "0 celcius" kelvin 273.15 [ <api url> ]
// 	Arg 1:	question degrees and unit separated by a space.
//		This argument must be enclosed in double quotes.
//	Arg 2:	answer must be in this unit
//	Arg 3:	student's answer in degrees
//	Arg 4 (optional): The url of the API.
//			The default is 'http://biterscripting.com/temp/tempserver.php'.

// How many args did we get ? We need min 4 (in which case, we will use
// default API location) or max 5 (in which case, the user has specified
// API location).

if ($argc == 4)
	$url = 'http://biterscripting.com/temp/tempserver.php' ;
elseif ($argc == 5)
	$url = $argv[4];
else
	exit('tempclient.php: ERROR: '
		.'Invalid number of arguments'."\n"
		.'Syntax: php.exe tempclient.php <question_degrees> <question_unit>'
		."\n\t".'<answer_unit> <answer_degrees> [ <API url> ]');

// Create JSON array using other arguments.
$data = array(	// We are using associative array.
	'q'=> $argv[1],
	'a_unit'=> $argv[2],
	'a_degrees'=> $argv[3],
	'grade'=> ''
	);
 
$options = array(
	'http' => array(
		'method'  => 'POST',
		'content' => json_encode( $data ),
		'header'=>  'Content-Type: application/json\r\n' .
				'Accept: application/json\r\n'
		)
	);

$context  = stream_context_create( $options );
$result = file_get_contents( $url, false, $context );
$response = json_decode($result, true);
echo 'Grade: ' . $response['grade'];

?>
