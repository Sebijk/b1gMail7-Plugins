<?php
/*
 * b1gMail HTTPMail
 * (c) 2021 Patrick Schlangen et al
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

/**
 * HTTPMail plugin
 *
 */
class HTTPMailPlugin extends BMPlugin
{
	function __construct()
	{
		global $lang_admin;

		// plugin info
		$this->type					= BMPLUGIN_DEFAULT;
		$this->name					= 'HTTPMail';
		$this->author				= 'b1gMail Project';
		$this->web					= 'https://www.b1gmail.org/';
		$this->mail					= 'info@b1gmail.org';
		$this->version				= '1.0';
        $this->designedfor         	= '7.4.0';

		// group option
		$this->RegisterGroupOption('httpmail',
			FIELD_CHECKBOX,
			'HTTPMail?');
	}
}

/**
 * register plugin
 */
$plugins->registerPlugin('HTTPMailPlugin');
?>