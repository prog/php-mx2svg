<?php

namespace com\peterbodnar\mx2svg;

use com\peterbodnar\mx\IMatrix;
use com\peterbodnar\svg\Svg;



/**
 * Matrix SVG renderer
 */
class MxToSvg {


	/** @var string[] */
	protected $defaultPaths;


	/**
	 * @param string[] $defaultPaths
	 */
	public function __construct($defaultPaths = [1 => '#000']) {
		$this->defaultPaths = $defaultPaths;
	}


	/**
	 * Render matrix to svg.
	 *
	 * @param IMatrix $matrix ~ Matrix.
	 * @param string[] $paths ~ Path colors.
	 * @return Svg
	 */
	public function render(IMatrix $matrix, $paths = NULL) {
		if (NULL === $paths) {
			$paths = $this->defaultPaths;
		}
		return Utils::renderSvg($matrix, $paths);
	}

}
