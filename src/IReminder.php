<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace BlueSpice\Reminder;

interface IReminder {
	/**
	 * return string
	 */
	public function getType();

	/**
	 * return string
	 */
	public function getLabelMsgKey();
}
