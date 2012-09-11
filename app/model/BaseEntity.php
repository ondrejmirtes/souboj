<?php

/**
 * @MappedSuperClass
 */
abstract class BaseEntity extends \Nette\Object
{

	/**
	 * @var int
	 *
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue(strategy="AUTO")
	 */
	private $id;

	public function getId()
	{
		return $this->id;
	}

}
