<?php

namespace FrontModule;

use Nette\Application\UI\Form;

class BasketPresenter extends BasePresenter
{

	const FROM_MAIL = 'eshop@mirtes.cz';

	const TO_MAIL = 'ondrej@mirtes.cz';

	protected function createComponentRemoveForm()
	{
		$form = new Form();
		foreach ($this->getSession('basket')->items as $productId => $amount) {
			$form->addSubmit('remove' . $productId, 'Remove');
		}
		$form->addProtection();
		$form->onSuccess[] = array($this, 'submitRemoveForm');
		return $form;
	}

	public function submitRemoveForm(Form $form)
	{
		$submittedBy = $form->submitted;
		$productId = substr($submittedBy->name, strlen('basket'));

		$session = $this->getSession('basket');
		unset($session->items[$productId]);
		$this->flashMessage('Item removed');
		$this->redirect('default');
	}

	public function renderDefault()
	{
		$this->template->items = $this->getSession('basket')->items;
	}

	public function getProduct($id)
	{
		return $this->em->find('Product', $id);
	}

	public function actionPlaceOrder()
	{
		$items = $this->getSession('basket')->items;
		if (!$items || count($items) == 0) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
	}

	protected function createComponentPlaceOrderForm()
	{
		$form = new Form();
		$form->addText('name', 'Name')
			->setRequired('Name is required')
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_LENGTH, 'Name must be %d characters long at max', 100);

		$form->addTextarea('address', 'Address')
			->setRequired('Address is required')
			->addCondition(Form::FILLED)
				->addRule(Form::MAX_LENGTH, 'Address must be %d characters long at max', 500);

		$form->addSelect('paymentMethod', 'Payment method', \PaymentMethodEnum::getValues())
			->setPrompt('Select a payment method')
			->setRequired('Payment method is required');

		$form->addText('ccCode', 'CC code')
			->addConditionOn($form['paymentMethod'], Form::RANGE, array(\PaymentMethodEnum::CARD_SHOP, \PaymentMethodEnum::CARD_POSTOFFICE))
			->addRule(Form::FILLED, 'CC code is required')
			->addRule(Form::PATTERN, 'Invalid CC code', '([0-9]{4}( )?){4}');

		$form->addText('ccExpirationMonth', 'CC expiration month')
			->addConditionOn($form['paymentMethod'], Form::RANGE, array(\PaymentMethodEnum::CARD_SHOP, \PaymentMethodEnum::CARD_POSTOFFICE))
			->addRule(Form::FILLED, 'CC expiration month is required')
			->addRule(Form::PATTERN, 'Invalid CC expiration month (mm)', '(0[1-9]{1})|(1[0-2]{1})');

		$form->addText('ccExpirationYear', 'CC expiration year')
			->addConditionOn($form['paymentMethod'], Form::RANGE, array(\PaymentMethodEnum::CARD_SHOP, \PaymentMethodEnum::CARD_POSTOFFICE))
			->addRule(Form::FILLED, 'CC expiration year is required')
			->addRule(Form::PATTERN, 'Invalid CC expiration year format (yyyy)', '[0-9]{4}')
			->addRule(Form::RANGE, 'Invalid CC expiration year range (2012-2020)', array(2012, 2020));

		$form->addText('ccCvc', 'CC CVC')
			->addConditionOn($form['paymentMethod'], Form::RANGE, array(\PaymentMethodEnum::CARD_SHOP, \PaymentMethodEnum::CARD_POSTOFFICE))
			->addRule(Form::FILLED, 'CVC is required')
			->addRule(Form::INTEGER, 'CVC must be an integer');

		$form->addSubmit('submit', 'Place an order');
		$form->onSubmit[] = array($this, 'submitPlaceOrderForm');
		return $form;
	}

	public function submitPlaceOrderForm(Form $form)
	{
		$values = $form->getValues();

		$order = new \Order();

		if ($this->user->loggedIn) {
			$user = $this->em->find('User', $this->user->identity->getId());
			$order->setUser($user);
		}

		$order->setStatus(\OrderStatusEnum::NOT_TAKEN_OVER);
		$order->setName($values['name']);
		$order->setAddress($values['address']);
		$order->setPaymentMethod($values['paymentMethod']);

		$items = array();
		foreach ($this->getSession('basket')->items as $productId => $amount) {
			$product = $this->getProduct($productId);
			if ($amount > $product->amount) {
				$form->addError('Cannot process the order. We do not have a product in stock.');
				return;
			}
			$item = new \OrderItem();
			$item->setProduct($product);
			$item->setOrder($order);
			$item->setAmount($amount);
			$item->setPrice($product->price);
			$this->em->persist($item);

			$product->setAmount($product->getAmount() - $amount);

			$items[] = $item;
		}

		$this->em->persist($order);
		$this->em->flush();

		$this->getSession('basket')->items = array();

		$this->sendMail($items);

		$this->flashMessage('Order successfully placed. Thanks!');
		$this->redirect('Homepage:');
	}

	public function sendMail(array $items)
	{
		$mail = new \Nette\Mail\Message();
		$mail->setFrom(self::FROM_MAIL);
		$mail->addTo(self::TO_MAIL);
		$mail->setSubject('Nová objednávka');

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/../templates/order.latte');
		$template->items = $items;

		$mail->setHtmlBody((string) $template);
		$mail->send();
	}

}
