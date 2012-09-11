<?php

use Nette\DI\Container;

class Authenticator extends \Nette\Object implements \Nette\Security\IAuthenticator
{

	private $em;

	public function __construct(Container $container)
	{
		$this->em = $container->em;
	}

	public function authenticate(array $credentials)
	{
		$user = $this->em->getRepository('User')->findOneBy(array(
			'username' => $credentials[self::USERNAME],
		));

		if ($user === NULL) {
			throw new \Nette\Security\AuthenticationException('User not found.');
		}

		if (!$user->isEqualPassword($credentials[self::PASSWORD])) {
			throw new \Nette\Security\AuthenticationException('Passwords do not match.');
		}

		if (!$user->admin) {
			throw new \Nette\Security\AuthenticationException('User is not admin.');
		}

		return new \Nette\Security\Identity($user->id, 'admin', array(
			'name' => $user->username,
		));
	}

}