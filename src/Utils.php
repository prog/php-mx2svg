<?php

namespace com\peterbodnar\mx2svg;

use com\peterbodnar\mx\IMatrix;
use com\peterbodnar\svg\Svg;
use SplFixedArray;



/**
 * Matrix to SVG rendering utils
 *
 * @internal
 */
class Utils {


	const BLOCK_TL = 0b0001;
	const BLOCK_TR = 0b0010;
	const BLOCK_BR = 0b0100;
	const BLOCK_BL = 0b1000;
	const DIR_VERT = 0b10;
	const DIR_BACK = 0b01;

	const BLOCK_T = 0b0011; // BLOCK_TL | BLOCK_TR
	const BLOCK_B = 0b1100; // BLOCK_BL | BLOCK_BR
	const BLOCK_L = 0b1001; // BLOCK_TL | BLOCK_BL
	const BLOCK_R = 0b0110; // BLOCK_TR | BLOCK_BR
	const BLOCK_ALL = 0b1111; // BLOCK_TL | BLOCK_TR | BLOCK_BR | BLOCK_BL
	const DIR_R = 0b00; // 0
	const DIR_L = 0b01; // DIR_BACK
	const DIR_D = 0b10; // DIR_VERTICAL
	const DIR_U = 0b11; // DIR_VERTICAL | DIR_BACK


	/**
	 * Return the shortest string from specified array.
	 *
	 * @internal
	 *
	 * @param string[] $strings
	 * @return string
	 */
	static public function shortest(array $strings) {
		return array_reduce($strings, function($carry, $item) {
			return (NULL === $carry || strlen($item) < strlen($carry)) ? $item : $carry;
		});
	}


	/**
	 * Move cursor to specified position.
	 *
	 * @internal
	 *
	 * @param int $x ~ Target position.
	 * @param int $y ~ Target position.
	 * @param int|null $x0 ~ Current cursor position.
	 * @param int|null $y0 ~ Current cursor position.
	 * @return string
	 */
	static public function moveTo($x, $y, $x0 = NULL, $y0 = NULL) {
		$options = [$y < 0 ? "M{$x}{$y}" : "M{$x} {$y}"];
		if (NULL !== $x0 && NULL !== $y0) {
			$dx = ($x - $x0);
			$dy = ($y - $y0);
			$options[] = ($dy < 0 ? "m{$dx}{$dy}" : "m{$dx} {$dy}");
		}
		return static::shortest($options);
	}


	/**
	 * Draw line.
	 *
	 * @internal
	 *
	 * @param int $x ~ Target position.
	 * @param int $y ~ Target position.
	 * @param int $x0 ~ Current cursor position.
	 * @param int $y0 ~ Current cursor position.
	 * @return string
	 */
	static public function lineTo($x, $y, $x0, $y0) {
		$options = [$y < 0 ? "L{$x}{$y}" : "L{$x} {$y}"];
		$dx = ($x - $x0);
		$dy = ($y - $y0);
		if (0 === $dx) {
			$options[] = "V{$y}";
			$options[] = "v{$dy}";
		} elseif (0 === $dy) {
			$options[] = "H{$x}";
			$options[] = "h{$dx}";
		}
		return static::shortest($options);
	}


	/**
	 * Return adjacent blocks info.
	 *
	 * @internal
	 *
	 * @param IMatrix $matrix ~ Matrix.
	 * @param mixed $val ~ Value.
	 * @param int $x ~ Column index.
	 * @param int $y ~ Row index.
	 * @return int
	 */
	static public function getBlocks(IMatrix $matrix, $val, $x, $y) {
		$w = $matrix->getColumns();
		$h = $matrix->getRows();
		return
			  (($x > 0) && ($y > 0) && ($val === $matrix->getValue($y - 1, $x - 1)) ? static::BLOCK_TL : 0)
			| (($x < $w) && ($y > 0) && ($val === $matrix->getValue($y - 1, $x)) ? static::BLOCK_TR : 0)
			| (($x > 0) && ($y < $h) && ($val === $matrix->getValue($y, $x - 1)) ? static::BLOCK_BL : 0)
			| (($x < $w) && ($y < $h) && ($val === $matrix->getValue($y, $x)) ? static::BLOCK_BR : 0);
	}


	/**
	 * Return new direction by current direction and blocks around current position.
	 *
	 * @internal
	 *
	 * @param int $dir ~ Current direction.
	 * @param int $blocks ~ Blocks around current position.
	 * @return int
	 */
	static protected function getDir($dir, $blocks) {
		$turns = [static::BLOCK_TL, static::BLOCK_TR, static::BLOCK_BR, static::BLOCK_BL];
		if (!in_array($blocks, $turns, true) && !in_array(($blocks = static::BLOCK_ALL & ~$blocks), $turns)) {
			return $dir;
		}
		$dirVert = !($dir & static::DIR_VERT);
		$dirBack = $blocks & ($dirVert ? static::BLOCK_T : static::BLOCK_L);
		return ($dirVert ? static::DIR_VERT : 0) | ($dirBack ? static::DIR_BACK : 0);
	}


	/**
	 * Render one segment of matrix starting from specified position.
	 *
	 * @internal
	 *
	 * @param IMatrix $matrix ~ Matrix.
	 * @param mixed $val ~ Value.
	 * @param int $x ~ Starting position
	 * @param int $y ~ Start
	 * @param SplFixedArray $visited ~ Array to save visited positions.
	 * @return string
	 */
	static public function renderSegment(IMatrix $matrix, $val, $x, $y, SplFixedArray $visited) {
		$result = "";

		$w = $matrix->getColumns();
		$dir = static::DIR_R;
		$startX = $x0 = $x;
		$startY = $y0 = $y;

		for (;;) {
			$step = ($dir & static::DIR_BACK) ? -1 : 1;
			if ($dir & static::DIR_VERT) {
				$y += $step;
			} else {
				$x += $step;
			}
			if ($x === $startX && $y === $startY) {
				return $result . "z";
			}
			$visited[$y * ($w + 1) + $x] = true;
			$b = static::getBlocks($matrix, $val, $x, $y);
			$newDir = static::getDir($dir, $b);
			if ($newDir !== $dir) {
				$result .= static::lineTo($x, $y, $x0, $y0);
				$x0 = $x;
				$y0 = $y;
				$dir = $newDir;
			}
		}
	}


	/**
	 * Render svg path directives for specifed value in matrix.
	 *
	 * @internal
	 *
	 * @param IMatrix $matrix
	 * @param mixed $val
	 * @return string
	 */
	static public function renderPath(IMatrix $matrix, $val) {
		$result = "";
		$curX = NULL;
		$curY = NULL;
		$w = $matrix->getColumns();
		$h = $matrix->getRows();
		$visited = new SplFixedArray(($w + 1) * ($h + 1));

		for ($y = 0; $y < $h; $y++) {
			for ($x = 0; $x < $w; $x++) {
				if (isset($visited[$y * ($w + 1) + $x])) {
					continue;
				}
				$b = static::getBlocks($matrix, $val, $x, $y);
				if ((static::BLOCK_BR === $b) || ((static::BLOCK_ALL & ~static::BLOCK_BR) === $b)) {
					$result .= static::moveTo($x, $y, $curX, $curY);
					$result .= static::renderSegment($matrix, $val, $x, $y, $visited);
					$curX = $x;
					$curY = $y;
				}
			}
		}

		return $result;
	}


	/**
	 * Render path directives for specifed values in matrix.
	 *
	 * @internal
	 *
	 * @param IMatrix $matrix ~ Matrix.
	 * @param string[] $paths ~ Path colors.
	 * @return string
	 */
	public static function renderSvg(IMatrix $matrix, array $paths) {
		$w = $matrix->getColumns();
		$h = $matrix->getRows();

		$args = ["viewBox" => "0 0 {$w} {$h}"];
		$result = "";
		foreach ($paths as $pathValue => $pathColor) {
			$pathDirectives = static::renderPath($matrix, $pathValue);
			$result .= "<path fill-rule=\"evenodd\" fill=\"{$pathColor}\" d=\"{$pathDirectives}\"/>";
		}
		return new Svg($result, $args);
	}

}
