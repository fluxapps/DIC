<?php

namespace srag\DIC\Output;

use ilConfirmationGUI;
use ILIAS\UI\Component\Component;
use ilPropertyFormGUI;
use ilTable2GUI;
use ilTemplate;
use ilTemplateException;
use JsonSerializable;
use srag\DIC\Exception\DICException;
use stdClass;

/**
 * Interface OutputInterface
 *
 * @package srag\DIC\Output
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface OutputInterface {

	/**
	 * Output HTML or GUI
	 *
	 * @param string|ilTemplate|ilConfirmationGUI|ilPropertyFormGUI|ilTable2GUI|Component $value html or gui instance
	 * @param bool                                                                        $main  Display main skin?
	 *
	 * @throws DICException Class {get_class($value)} is not supported for output!
	 * @throws ilTemplateException
	 */
	public function output($value, /*bool*/
		$main = true)/*: void*/
	;


	/**
	 * Output JSON
	 *
	 * @param string|int|double|bool|array|stdClass|null|JsonSerializable $value html, gui instance or json value
	 *
	 * @throws DICException {get_class($value)} is not a valid JSON value!
	 */
	public function outputJSON($value)/*: void*/
	;
}
