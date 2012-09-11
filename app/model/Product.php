<?php

/**
 * @Entity
 * @Table(indexes={@index(name="category_idx", columns={"category_id", "visible"})})
 */
class Product extends BaseEntity
{

	/**
	 * @var string
	 * @Column(type="text")
	 */
	private $about;

	/**
	 * @var double
	 * @Column(type="decimal", precision=8, scale=0, nullable=true)
	 */
	private $amount;

	/**
	 * @var string
	 * @Column(length=100, nullable=true)
	 */
	private $name;

	/**
	 * @var double
	 * @Column(type="decimal", precision=10, scale=2, nullable=true)
	 */
	private $price;

	/**
	 * @var boolean
	 * @Column(type="boolean")
	 */
	private $visible;

	/**
	 * @var Product
	 * @ManyToOne(targetEntity="Category")
	 * @JoinColumn(name="category_id")
	 */
	private $category;

	public function __construct()
	{
		$this->visible = FALSE;
	}

	public function getAbout()
	{
		return $this->about;
	}

	public function setAbout($about)
	{
		$this->about = $about;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setAmount($amount)
	{
		$this->amount = (double) $amount;
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getPrice()
	{
		return $this->price;
	}

	public function setPrice($price)
	{
		$this->price = (double) $price;
	}

	public function getVisible()
	{
		return $this->visible;
	}

	public function setVisible($visible)
	{
		$this->visible = (bool) $visible;
	}

	public function getCategory()
	{
		return $this->category;
	}

	public function setCategory(Category $category = NULL)
	{
		$this->category = $category;
	}

}
