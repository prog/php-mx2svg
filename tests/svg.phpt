<?php

use com\peterbodnar\mx2svg;
use Tester\Assert;

require __DIR__ . "/inc/bootstrap.php";
require __DIR__ . "/inc/Matrix.php";



$testcases = [
	"RGB \n" .
	" RGB\n" .
	"B RG\n" .
	"GB R\n" =>
		"<svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 4 4\">" .
			"<path fill-rule=\"evenodd\" fill=\"#f00\" d=\"M0 0H1V2H3V4H4V3H2V1H0z\"/>" .
			"<path fill-rule=\"evenodd\" fill=\"#0f0\" d=\"M1 0H2V2H4V3H3V1H1zM0 3H1V4H0z\"/>" .
			"<path fill-rule=\"evenodd\" fill=\"#00f\" d=\"M2 0H3V2H4V1H2zM0 2H1V4H2V3H0z\"/>" .
		"</svg>",
];

$colors = [
	"R" => "#f00",
	"G" => "#0f0",
	"B" => "#00f",
];
$mxToSvg = new mx2svg\MxToSvg($colors);

foreach ($testcases as $matrixDef => $expectedResult) {
	$matrix = new Matrix(explode("\n", rtrim($matrixDef, "\n")));
	$actualResult = (string) $mxToSvg->render($matrix);
	Assert::equal($expectedResult, $actualResult);
}
