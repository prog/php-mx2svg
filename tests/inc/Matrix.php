<?php

use com\peterbodnar\mx\IMatrix;




class Matrix implements IMatrix {


	/** @var string[] */
	public $data;


	/**
	 * Matrix constructor.
	 *
	 * @param string[] $data
	 */
	public function __construct(array $data) {
		$this->data = $data;
	}


	public function getRows() {
		return count($this->data);
	}


	public function getColumns() {
		return count($this->data) ? strlen($this->data[0]) : 0;
	}


	public function getValue($rowIndex, $columnIndex) {
		return isset($this->data[$rowIndex][$columnIndex]) ? $this->data[$rowIndex][$columnIndex] : NULL;
	}

}
