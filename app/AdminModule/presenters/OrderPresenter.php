<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class OrderPresenter extends BasePresenter
{

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	protected function startup()
	{
		parent::startup();
		$this->em = $this->context->em;
	}

	public function renderDefault()
	{
		$this->template->orders = $this->em->getRepository('Order')->findAll();
	}

	protected function createComponentAddForm()
	{
		$form = new Form();

		$form->addSelect('user', 'Users', $this->getUsers())
			->setPrompt('Select a user');

		$form->addText('name', 'Name')
			->setRequired('Name is required')
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_LENGTH, 'Name must be %d characters long at max', 100);

		$form->addTextarea('address', 'Address')
			->setRequired('Address is required')
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_LENGTH, 'Address must be %d characters long at max', 500);

		$form->addSelect('status', 'Status', \OrderStatusEnum::getValues())
			->setPrompt('Select order status')
			->setRequired('Order status is required');

		$form->addSelect('paymentMethod', 'Payment method', \PaymentMethodEnum::getValues())
			->setPrompt('Select payment method')
			->setRequired('Payment method is required');

		if ($this->getParameter('id')) {
			$form->addText('createdAt', 'Created at')
			->setRequired('Created at is required')
			->addCondition(Form::FILLED)
				->addRule(function($control) {
					try {
						new \DateTime($control->getValue());
						return TRUE;
					} catch (\Exception $e) {
						return FALSE;
					}
				}, 'Invalid Created at datetime format');

			$form->addText('variableNumber', 'Variable number')
				->setRequired('Variable number is required')
				->addRule(Form::MAX_LENGTH, 'Variable number must be %d digits long at max', 45)
				->addRule(Form::PATTERN, 'You can use numeric digits only', '[0-9]*');
		}

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitAddForm');

		return $form;
	}

	private function getUsers()
	{
		$users = array();
		foreach($this->em->getRepository('User')->findAll() as $user) {
			$users[$user->id] = $user->username;
		}
		return $users;
	}

	public function submitAddForm(Form $form)
	{
		$values = $form->getValues();
		$order = new \Order;

		$this->fillValues($order, $values);

		$this->em->persist($order);
		$this->em->flush();

		$this->flashMessage('Order added.');
		$this->redirect('default');
	}

	public function renderEdit($id)
	{
		$order = $this->findOrder($id);
		$this->template->order = $order;

		$this['editForm']->setDefaults(array(
			'name' => $order->name,
			'address' => $order->address,
			'createdAt' => $order->createdAt->format('Y-m-d H:i'),
			'status' => $order->status,
			'paymentMethod' => $order->paymentMethod,
			'variableNumber' => $order->variableNumber,
			'user' => $order->user ? $order->user->id : NULL,
		));
	}

	private function findOrder($id)
	{
		$order = $this->em->find('Order', (int) $id);
		if ($order === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $order;
	}

	protected function createComponentEditForm()
	{
		$form = $this->createComponentAddForm();
		$form->onSuccess = array();
		$form->onSuccess[] = array($this, 'submitEditForm');
		return $form;
	}

	public function submitEditForm(Form $form)
	{
		$values = $form->getValues();
		$order = $this->findOrder($this->getParameter('id'));

		$this->fillValues($order, $values);

		$this->em->flush();

		$this->flashMessage('Order updated.');
		$this->redirect('default');
	}

	private function fillValues(\Order $order, $values)
	{
		$order->setName($values['name']);
		$order->setAddress($values['address']);
		if (isset($values['createdAt'])) {
			$order->setCreatedAt(new \DateTime($values['createdAt']));
		}
		$order->setStatus($values['status']);
		$order->setPaymentMethod($values['paymentMethod']);
		if (isset($values['variableNumber'])) {
			$order->setVariableNumber($values['variableNumber']);
		}
		if ($values['user']) {
			$user = $this->em->find('User', $values['user']);
			$order->setUser($user);
		} else {
			$order->setUser(NULL);
		}
	}

	public function renderDelete($id)
	{
		$this->template->order = $this->findOrder($id);
	}

	protected function createComponentDeleteForm()
	{
		$form = new Form();
		$form->addProtection('CSRF token invalid.');
		$form->addSubmit('submit', 'Delete');
		$form->onSubmit[] = array($this, 'submitDeleteForm');

		return $form;
	}

	public function submitDeleteForm()
	{
		$order = $this->findOrder($this->getParameter('id'));
		$this->em->remove($order);
		$this->em->flush();

		$this->flashMessage('Order deleted');
		$this->redirect('default');
	}

	public function renderAddItem($orderId)
	{
		$this->template->order = $this->findOrder($orderId);
	}

	protected function createComponentAddItemForm()
	{
		$form = new Form();
		$form->addSelect('product', 'Product', $this->getProducts())
			->setPrompt('Select a product')
			->setRequired('Product is required');

		$that = $this;
		$form->addText('amount', 'Amount')
			->setRequired('Amount is required')
			->addCondition(Form::FILLED)
				->addRule(Form::INTEGER, 'Amount must be an integer')
				->addRule(function($control) use ($form, $that) {
					$value = $control->getValue();
					$product = $that->findProduct($form['product']->getValue());
					return $value <= $product->amount;
				}, 'Amount is greater than the product has in stock');

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitAddItemForm');
		return $form;
	}

	private function getProducts()
	{
		$products = array();
		foreach($this->em->getRepository('Product')->findAll() as $product) {
			$products[$product->id] = $product->name;
		}
		return $products;
	}

	public function findProduct($id)
	{
		$product = $this->em->find('Product', (int) $id);
		if ($product === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $product;
	}

	public function submitAddItemForm(Form $form)
	{
		$values = $form->getValues();
		$item = new \OrderItem;

		$item->setOrder($this->findOrder($this->getParameter('orderId')));

		$product = $this->findProduct($values['product']);
		$item->setProduct($product);
		$item->setAmount($values['amount']);
		$item->setPrice($product->getPrice());
		$product->setAmount($product->getAmount() - (int) $values['amount']);

		$this->em->persist($item);
		$this->em->flush();

		$this->flashMessage('Item added');
		$this->redirect('edit', $this->getParameter('orderId'));
	}

	public function renderEditItem($itemId)
	{
		$item = $this->findItem($itemId);
		$this->template->item = $item;
		$this->template->order = $item->order;

		$this['editItemForm']->setDefaults(array(
			'price' => $item->price,
			'amount' => $item->amount,
		));
	}

	private function findItem($id)
	{
		$item = $this->em->find('OrderItem', (int) $id);
		if ($item === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $item;
	}

	protected function createComponentEditItemForm()
	{
		$form = new Form();
		$form->addText('price', 'Price')
			->setRequired('Price is required')
			->addRule(Form::FLOAT, 'Price must be a float value');

		$form->addText('amount', 'Amount')
			->setRequired('Amount is required')
			->addRule(Form::INTEGER, 'Amount must be an integer');

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitEditItemForm');

		return $form;
	}

	public function submitEditItemForm(Form $form)
	{
		$values = $form->values;
		$item = $this->findItem($this->getParameter('itemId'));
		$item->setPrice($values['price']);
		$item->setAmount($values['amount']);

		$this->em->flush();
		$this->flashMessage('Item updated');
		$this->redirect('edit', $item->order->id);
	}

	public function renderDeleteItem($itemId)
	{
		$item = $this->findItem($itemId);
		$this->template->item = $item;
		$this->template->order = $item->order;
	}

	protected function createComponentDeleteItemForm()
	{
		$form = new Form();
		$form->addProtection('CSRF token invalid.');
		$form->addSubmit('submit', 'Delete');
		$form->onSubmit[] = array($this, 'submitDeleteItemForm');

		return $form;
	}

	public function submitDeleteItemForm()
	{
		$item = $this->findItem($this->getParameter('itemId'));
		$this->em->remove($item);
		$this->em->flush();

		$this->flashMessage('Item deleted');
		$this->redirect('edit', $item->order->id);
	}

}
