<?php

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{

	protected $em;

	protected function startup()
	{
		parent::startup();
		$this->em = $this->context->em;
	}

	public function getProductCount($productId)
	{
		$params = array(
			'id' => $productId,
		);
		if (!$this->user->loggedIn || !$this->user->isInRole('admin')) {
			$params['visible'] = TRUE;
		}
		$product = $this->em->getRepository('Product')->findOneBy($params);
		if ($product === NULL) {
			throw new \Nette\Application\BadRequestException();
		}

		return count($product->getOrders());
	}

}
