<?php

namespace FrontModule;

class CategoryPresenter extends BasePresenter
{

	public function renderDefault($id)
	{
		$category = $this->em->find('Category', (int) $id);
		if ($category === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		$this->template->category = $category;
		$this->template->products = $this->em->getRepository('Product')
			->findBy(array('category' => $category->id, 'visible' => TRUE));
	}

	public function actionAddToBasket($productId)
	{
		$product = $this->em->find('Product', $productId);
		if ($product === NULL) {
			throw new \Nette\Application\BadRequestException;
		}

		$session = $this->getSession('basket');
		if (!isset($session->items)) {
			$session->items = array();
		}
		if (!isset($session->items[$productId])) {
			$session->items[$productId] = 0;
		}

		if ($session->items[$productId] + 1 > $product->amount) {
			$this->flashMessage('We do not have that many products of this type in stock');
		} else {
			$session->items[$productId]++;
			$this->flashMessage('Product added to basket');
		}

		$this->redirect('default', $product->category->id);
	}

}
