<?php

use com\peterbodnar\mx2svg;
use Tester\Assert;

require __DIR__ . "/inc/bootstrap.php";
require __DIR__ . "/inc/Matrix.php";



$testcases = [
	"   \n" .
	" X \n" .
	"   \n" => "M1 1H2V2H1z",

	"   \n" .
	"XXX\n" .
	"   \n" => "M0 1H3V2H0z",

	"XXX\n" .
	"XXX\n" .
	"XXX\n" => "M0 0H3V3H0z",

	"XXX\n" .
	"   \n" .
	"XXX\n" => "M0 0H3V1H0zM0 2H3V3H0z",

	"XXX\n" .
	"X X\n" .
	"XXX\n" => "M0 0H3V3H0zM1 1H2V2H1z",

	"X X\n" .
	" X \n" .
	"X X\n" => "M0 0H1V3H0V2H3V3H2V0H3V1H0z",

	" X \n" .
	"X X\n" .
	" X \n" => "M1 0H2V3H1zM0 1H3V2H0z",
];


foreach ($testcases as $matrixDef => $expectedResult) {
	$matrix = new Matrix(explode("\n", rtrim($matrixDef, "\n")));
	$actualResult = mx2svg\Utils::renderPath($matrix, "X");
	Assert::equal($expectedResult, $actualResult);
}
