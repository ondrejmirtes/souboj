<?php

namespace AdminModule;

use Nette\Application\UI\Form;

class ProductPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->products = $this->em->getRepository('Product')->findAll();
	}

	protected function createComponentAddForm()
	{
		$form = new Form();

		$form->addTextarea('about', 'About')
			->setRequired('About is required');

		$form->addText('amount', 'Amount')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Amount must be integer or float');

		$form->addText('name', 'Name')
			->addRule(Form::MAX_LENGTH, 'Name must be %d characters long at max.', 100);

		$form->addText('price', 'Price')
			->addCondition(Form::FILLED)
				->addRule(Form::FLOAT, 'Price must be integer or float');

		$form->addCheckbox('visible', 'Visible');

		$form->addSelect('category', 'Category', $this->getCategories())
			->setPrompt('Select a category');

		$form->addText('deliveryDate', 'Delivery date')
			->addRule(Form::MAX_LENGTH, 'Delivery date must be %d characters long at max.', 100);

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitAddForm');

		return $form;
	}

	private function getCategories()
	{
		$categories = array();
		foreach($this->em->getRepository('Category')->findAll() as $category) {
			$categories[$category->id] = $category->name;
		}
		return $categories;
	}

	public function submitAddForm(Form $form)
	{
		$values = $form->getValues();
		$product = new \Product;

		$this->fillValues($product, $values);

		$this->em->persist($product);
		$this->em->flush();

		$this->flashMessage('Product added.');
		$this->redirect('default');
	}

	public function renderEdit($id)
	{
		$product = $this->findProduct($id);
		$this->template->product = $product;

		$this['editForm']->setDefaults(array(
			'about' => $product->about,
			'amount' => $product->amount,
			'name' => $product->name,
			'price' => $product->price,
			'visible' => $product->visible,
			'category' => $product->category ? $product->category->id : NULL,
			'deliveryDate' => $product->deliveryDate,
		));
	}

	private function findProduct($id)
	{
		$product = $this->em->find('Product', (int) $id);
		if ($product === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $product;
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
		$product = $this->findProduct($this->getParameter('id'));

		$this->fillValues($product, $values);

		$this->em->flush();

		$this->flashMessage('Product updated.');
		$this->redirect('default');
	}

	private function fillValues(\Product $product, $values)
	{
		$product->setAbout($values['about']);
		$product->setAmount($values['amount']);
		$product->setName($values['name']);
		$product->setPrice($values['price']);
		$product->setVisible($values['visible']);

		if ($values['category']) {
			$category = $this->em->find('Category', $values['category']);
			$product->setCategory($category);
		} else {
			$product->setCategory(NULL);
		}

		$product->setDeliveryDate($values['deliveryDate']);
	}

	public function renderDelete($id)
	{
		$this->template->product = $this->findProduct($id);
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
		$product = $this->findProduct($this->getParameter('id'));
		$this->em->remove($product);
		$this->em->flush();

		$this->flashMessage('Product deleted');
		$this->redirect('default');
	}

}
