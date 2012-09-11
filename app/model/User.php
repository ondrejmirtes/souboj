<?php

use Nette\Utils\Strings;

/**
 * @Entity
 * @Table(name="`user`")
 */
class User extends BaseEntity
{

	/**
	 * @var string
	 * @Column(length=200, unique=true)
	 */
	private $username;

	/**
	 * @var string
	 * @Column(length=64)
	 */
	private $password;

	/**
	 * @var string
	 * @Column(length=10)
	 */
	private $salt;

	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	private $admin;

	public function __construct()
	{
		$this->admin = FALSE;
	}

	public function setCredentials($username, $password)
	{
		$this->username = $username;
		$this->salt = Strings::random(10);
		$this->password = self::hashPassword($password, $this->salt);
	}

	public static function hashPassword($password, $salt)
	{
		return hash_hmac('sha256', $password, $salt);
	}

	public function getAdmin()
	{
		return $this->admin;
	}

	public function setAdmin($admin)
	{
		$this->admin = (bool) $admin;
	}

}
