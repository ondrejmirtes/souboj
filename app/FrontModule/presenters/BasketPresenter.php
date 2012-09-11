<?php

namespace FrontModule;

use Nette\Application\UI\Form;

class BasketPresenter extends BasePresenter
{

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

}
