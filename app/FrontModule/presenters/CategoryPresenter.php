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

}