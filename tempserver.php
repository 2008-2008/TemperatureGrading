<?php

/* API for checking student answers in the temperature conversion class.

We will take in student answers with these -

- Input temperature (degrees) along with its unit. The temperature is a decimal field.
	The unit is one of the following - Kelvin/Celsius/Farenheit/Rankine.
	These 2 values, separated by a space, are sent in as ONE field.
- Response unit. This is the unit the student is expected to convert the
	degrees to.
- Response temperature. This is the student response. Decimal field.
	Student is expected to convert input temperature in input units to
	temperature in response unit

We will use this nomenclature: question, answer
The question refers to the input. The answer refers to response (student's answer).
We will use these names consistently.
q		Input temperature in degrees along with its unit.
a_unit		The unit in which the student's answer is expected in.
a_degrees	The student's answer.

We will stay away from using the words 'request', 'response' in variable names,
since that is confusing with HTTP request/response.

The API will return the following 
- Grade (Correct/Incorrect/Invalid)

We will name this variable grade.

Data is interchanged in JSON format. The method is POST.

This is the JSON structure we will use.
		$data = array(	// We are using associative array.
			'q'=> q_degrees+' '+q_unit,
			'a_unit'=> a_unit,
			'a_degrees'=> a_degrees,
			'grade'=> ''
		);

*/


// Get data in json format.
$data = json_decode(file_get_contents('php://input'), true);
	// We use true so we get the associative array.

$q = $data['q'];
$a_unit = $data['a_unit'];
$a_degrees = $data['a_degrees'];
$grade = 'Invalid';	// By default

// We will keep a log.
file_put_contents('./templog_server.txt',
	'TEMPSERVER: INFO: '.date('YmdGis').': Received: '.$q.'|'.$a_unit.'|'.$a_degrees."\n",
		// escaped characters require double quote.
	FILE_APPEND);

// While processing, we may encounuter invalid or incorrect situations.
// Instead of having nested ifs (which make code difficult to read),
// we will use do-break. Further, this way, we will have our code
// for response, only in one place and not repeated in many places.

do
{
	// Is any string empty ? If student's answer is empty, that's Incorrect.
	// Otherwise, it is invalid.
	if ( (strlen($q)==0) || (strlen($a_unit)==0) )
		break;
		// Note that grade is already set to 'Invalid' above.

	if (strlen($a_degrees)==0)
	{
		$grade = 'Incorrect';
		break;
	}

	// Trim all.
	$q = trim($q);
	$a_unit = trim($a_unit);
	$a_degrees = trim($a_degrees);

	// Separate q into q_degrees and q_unit.
	$i = strpos($q, ' ');

	// Is there a space ? Also, it should not be at position 0.
	if ($i <= 0)
		break;

	$q_degrees = substr($q, 0, $i);
	$q_unit = substr($q, $i+1);

	// Is any field empty ? If so, this is 'Invalid'.
	if ( (strlen($q_degrees)==0) || (strlen($q_unit)==0) )
		break;

	// Now check all fields one by one.
	
	// q_degrees must be numeric
	if (! is_numeric($q_degrees))
		break;

	// a_degrees also must be numeric. But if it is not,
	// the grade should 'incorrect'.
	if (! is_numeric($a_degrees)) {
		$grade = 'Incorrect';
		break;
	}

	// q_unit must be one of the 4 units - Kelvin/Celsius/Farenheit/Rankine.
	// But if it is not, that's not student's error. It's teacher's error.
	// So, we will be more tolerant and check only first character. Further
	// we will check characters to the left and right of it on the keyboard -
	// this is the most common typing error.

	$q_unit0 = $q_unit[0];

	if (stristr('kjl', $q_unit0) != false)
		$q_unit0 = 'K';	// We are preparing for switch coming later.
				// Char comparison will be better performing
				// than complete string comparison.
	elseif (stristr('cxv', $q_unit0) != false)
		$q_unit0 = 'C';
	elseif (stristr('fdg', $q_unit0) != false)
		$q_unit0 = 'F';
	elseif (stristr('ret', $q_unit0) != false)
		$q_unit0 = 'R';
	else
		break;

	// Do the same for a_unit.
	$a_unit0 = $a_unit[0];

	if (stristr('kjl', $a_unit0) != false)
		$a_unit0 = 'K';	// We are preparing for switch coming later.
				// Char comparison will be better performing
				// than complete string comparison.
	elseif (stristr('cxv', $a_unit0) != false)
		$a_unit0 = 'C';
	elseif (stristr('fdg', $a_unit0) != false)
		$a_unit0 = 'F';
	elseif (stristr('ret', $a_unit0) != false)
		$a_unit0 = 'R';
	else
		break;

	// All validations are performed.
	// Do the actual conversions.
	// Convert degrees to numbers.
	$q_degrees_num = floatval($q_degrees);	// Type casting can also be
			// used. This is more safe.
	$a_degrees_num = floatval($a_degrees);

	// For conversion formulas, use
	// https://www.metric-conversions.org/temperature/celsius-to-kelvin.htm

	// If we convert from q_unit to a_unit directly, it will require
	// a nested switch with 4*4=16 cases. Instead for simplicity, we will 
	// convert q_degrees to Kelvin first, then to the target unit. This will
	// result in linear 8 cases.

	// However, we will do this only if the input and answer units 
	// are different.

	if ($q_unit0 != $a_unit0) {
		switch($q_unit0)
		{
		case 'C': $q_degrees_num = $q_degrees_num + 273.15;
			// K =C+ 273.15
			break;
		case 'F': $q_degrees_num = (($q_degrees_num - 32)/1.8000)+ 273.15;
			// K =((F - 32)/1.8000)+ 273.15
			break;
		case 'R': $q_degrees_num = (($q_degrees_num - 491.67)/1.8000)+ 273.15;
			// K =((R - 491.67)/1.8000)+ 273.15
			break;
		default:
			break;
		} // Default is K. We made sure above that q_unit0 must be one of these 4.

		// Now, convert q_degrees_num to the target unit.
		switch($a_unit0)
		{
		case 'C': $q_degrees_num = $q_degrees_num - 273.15;
			// C =K- 273.15
			break;
		case 'F': $q_degrees_num = (($q_degrees_num - 273.15)*1.8000) + 32;
			// F =((K - 273.15)*1.8000) + 32
			break;
		case 'R': $q_degrees_num = (($q_degrees_num - 273.15)*1.8000) + 491.67;
			// R = ((K - 273.15)*1.8000) + 491.67
			break;
		default:
			break;
		}
	} // END OF ($q_unit0 != $a_unit0)

	// Specs say that we are to compare rounded to one's place.
	$a_degrees_num = round($a_degrees_num, 0, PHP_ROUND_HALF_UP);
		// Apparently, intval() has some math problem.
	$q_degrees_num = round($q_degrees_num, 0, PHP_ROUND_HALF_UP);

	// Are these different ?
	if ($a_degrees_num != $q_degrees_num)
	{
		$grade = 'Incorrect';
		break;
	}

	// If we didn't break until now, woah, this student is getting an 'A' !
	$grade = 'Correct';
	break;	// Remember the do-while(true) trick ?

} while(true);

// We will log the grade too.
file_put_contents('./templog_server.txt',
	'TEMPSERVER: INFO: '.date('YmdGis').': Returning Grade: '.$grade."\n",
	FILE_APPEND);

// Everything is assigned. Package it up and ship it back to client.
$data['grade'] = $grade;
$json_response = json_encode($data);
echo $json_response;

// All done.
