<?php
/*
** OnceMon
** Copyright (C) 2014-2015 ISCAS
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


/**
 * Class containing methods for operations with agent.
 *
 * @package API
 */
class AgentManager {

	public static function send($address,$port,$msg) {
		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($socket === false) {
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
			return null;
		}
		$result = socket_connect($socket, $address, $port);
		if($result === false) {
			echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
			return null;
		}
		$out = "";
		socket_write($socket, $msg . "\n==END==\n");
		flush();

		$response = socket_read ( $socket, 4096, PHP_NORMAL_READ );

		socket_close($socket);

		return json_decode($response,true);
	}



}
