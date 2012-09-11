<?php

use Nette\Utils\Strings;

/**
 * @Entity
 * @Table(indexes={@index(name="parent", columns={"category_id"})})
 */
class Category extends BaseEntity
{

	/**
	 * @var \Category
	 * @ManyToOne(targetEntity="Category", inversedBy="children")
	 * @JoinColumn(name="category_id")
	 */
	private $parent;

	/**
	 * @var \Category[]
	 * @OneToMany(targetEntity="Category", mappedBy="parent")
	 */
	private $children;

	/**
	 * @var string
	 * @Column(length=200, nullable=true)
	 */
	private $name;

	/**
	 * @var string
	 * @Column(length=200, unique=true)
	 */
	private $url;

	public function __construct()
	{
		$this->children = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getParent()
	{
		return $this->parent;
	}

	public function setParent(Category $category = NULL)
	{
		$this->parent = $category;
	}

	public function getChildren()
	{
		return $this->children;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setUrl($url)
	{
		$this->url = Strings::webalize($url);
	}

}
