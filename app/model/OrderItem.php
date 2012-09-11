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

}
