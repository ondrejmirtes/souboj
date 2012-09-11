<?php

/**
 * @Entity
 * @Table(name="order_item")
 */
class OrderItem extends BaseEntity
{

	/**
	 * @var double
	 * @Column(type="decimal", precision=10, scale=2)
	 */
	private $price;

	/**
	 * @var int
	 * @Column(type="integer", nullable=true)
	 */
	private $amount;

	/**
	 * @var \Order
	 * @ManyToOne(targetEntity="Order", inversedBy="items")
	 * @JoinColumn(nullable=false)
	 */
	private $order;

	/**
	 * @var \Product
	 * @ManyToOne(targetEntity="Product")
	 * @JoinColumn(nullable=false)
	 */
	private $product;

	public function getPrice()
	{
		return $this->price;
	}

	public function setPrice($price)
	{
		$this->price = $price;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function setAmount($amount)
	{
		$this->amount = $amount;
	}

	public function getOrder()
	{
		return $this->order;
	}

	public function setOrder(Order $order)
	{
		$this->order = $order;
	}

	public function getProduct()
	{
		return $this->product;
	}

	public function setProduct(Product $product)
	{
		$this->product = $product;
	}

}
