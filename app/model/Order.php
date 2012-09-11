<?php

use Nette\Utils\Strings;

/**
 * @Entity
 * @Table(name="`order`", indexes={@index(name="order_user_idx", columns={"user_id"})})
 */
class Order extends BaseEntity
{

	/**
	 * @var string
	 * @Column(length=100)
	 */
	private $name;

	/**
	 * @var string
	 * @Column(length=500)
	 */
	private $address;

	/**
	 * @var \DateTime
	 * @Column(name="created_at", type="datetimetz")
	 */
	private $createdAt;

	/**
	 * @var int
	 * @Column(type="integer")
	 */
	private $status;

	/**
	 * @var int
	 * @Column(name="payment_method", type="integer")
	 */
	private $paymentMethod;

	/**
	 * @var string
	 * @Column(length=45)
	 */
	private $variableNumber;

	/**
	 * @var \User
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="user_id")
	 */
	private $user;

	/**
	 * @var \Doctrine\Common\Collections\Collection
	 * @OneToMany(targetEntity="OrderItem", mappedBy="order")
	 */
	private $items;

	public function __construct()
	{
		$this->setCreatedAt(new \DateTime());
		$this->setVariableNumber(Strings::random(45, '0-9'));

		$this->items = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function getName()
	{
		return $this->name;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function getAddress()
	{
		return $this->address;
	}

	public function setAddress($address)
	{
		$this->address = $address;
	}

	public function getCreatedAt()
	{
		return $this->createdAt;
	}

	public function setCreatedAt(\DateTime $createdAt)
	{
		$this->createdAt = $createdAt;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStatus($status)
	{
		$status = (int) $status;
		if ($status < OrderStatusEnum::NOT_TAKEN_OVER || $status > OrderStatusEnum::SHIPPED) {
			throw new \InvalidArgumentException();
		}
		$this->status = $status;
	}

	public function getPaymentMethod()
	{
		return $this->paymentMethod;
	}

	public function setPaymentMethod($method)
	{
		$method = (int) $method;
		if ($method < PaymentMethodEnum::CASH_SHOP || $method > PaymentMethodEnum::CARD_POSTOFFICE) {
			throw new \InvalidArgumentException();
		}
		$this->paymentMethod = (int) $method;
	}

	public function getVariableNumber()
	{
		return $this->variableNumber;
	}

	public function setVariableNumber($variableNumber)
	{
		$this->variableNumber = $variableNumber;
	}

	public function getUser()
	{
		return $this->user;
	}

	public function setUser(\User $user)
	{
		$this->user = $user;
	}

	public function getItems()
	{
		return $this->items->toArray();
	}

}
