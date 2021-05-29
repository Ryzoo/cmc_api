<?php

namespace Socket\Models;

use Core\Model\User;
use Ratchet\ConnectionInterface;

class UserModel
{
	/** @var User */
	public $user;

	/** @var ConnectionInterface */
	public $client;
}