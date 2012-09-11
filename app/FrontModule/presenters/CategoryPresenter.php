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
			$message = 'We do not have that many products of this type in stock';
			unset($session->items[$productId]);
		} else {
			$session->items[$productId]++;
			$message = 'Product added to basket';
		}

		if (!$this->isAjax()) {
			$this->flashMessage($message);
			$this->redirect('default', $product->category->id);
		} else {
			$this->sendResponse(new \Nette\Application\Responses\JsonResponse(array(
				'message' => $message,
			)));
		}
	}

	public function actionProductCount($productId)
	{
		$this->sendResponse(new \Nette\Application\Responses\JsonResponse(array(
			'count' => $this->getProductCount($productId),
		)));
	}

	public function getProductCount($productId)
	{
		$product = $this->em->getRepository('Product')->findOneBy(array(
			'id' => $productId,
			'visible' => TRUE,
		));
		if ($product === NULL) {
			throw new \Nette\Application\BadRequestException();
		}

		return count($product->getOrders());
	}

}
