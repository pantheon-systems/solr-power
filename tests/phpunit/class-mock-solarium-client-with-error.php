<?php

use Solarium\QueryType\Update\Result as UpdateResult;

class Mock_Solarium_Client_With_Error extends Solarium\Client {
	public static $error_msg = "";

	public function update(Solarium\Core\Query\QueryInterface $update, $endpoint = null) : UpdateResult
	{
		throw new Exception(self::$error_msg);
	}
}