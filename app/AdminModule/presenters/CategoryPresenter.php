<?php

namespace AdminModule;

use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class CategoryPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->categories = $this->em->getRepository('Category')->findAll();
	}

	protected function createComponentAddForm()
	{
		$form = new Form();
		$form->addSelect('parent', 'Parent', $this->getParents())
			->setPrompt('Choose parent');

		$em = $this->em;
		$that = $this;

		$form->addText('name', 'Name');
		$form->addText('url', 'URL')
			->setRequired('URL is required')
			->addRule(function($control) use ($em, $that) {
				$value = Strings::webalize($control->getValue());
				$category = $em->getRepository('Category')->findOneByUrl($value);
				return $category === NULL
					|| $category->id == $that->getParameter('id');
			}, 'URL already exists');

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitAddForm');

		return $form;
	}

	public function submitAddForm(Form $form)
	{
		$values = $form->getValues();
		$category = new \Category;

		$this->fillValues($category, $values);

		$this->em->persist($category);
		$this->em->flush();

		$this->flashMessage('Category added.');
		$this->redirect('default');
	}

	private function getParents()
	{
		$parents = array();
		foreach($this->em->getRepository('Category')->findAll() as $category) {
			if ($this->getParameter('id') === NULL || $this->getParameter('id') != $category->id) {
				$parents[$category->id] = $category->name;
			}
		}
		return $parents;
	}

	public function renderEdit($id)
	{
		$category = $this->findCategory($id);
		$this->template->category = $category;

		$this['editForm']->setDefaults(array(
			'parent' => $category->getParent() ? $category->getParent()->getId() : NULL,
			'name' => $category->name,
			'url' => $category->url,
		));
	}

	private function findCategory($id)
	{
		$category = $this->em->find('Category', (int) $id);
		if ($category === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $category;
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
		$category = $this->findCategory($this->getParameter('id'));

		$this->fillValues($category, $values);

		$this->em->flush();

		$this->flashMessage('Category updated.');
		$this->redirect('default');
	}

	private function fillValues(\Category $category, $values)
	{
		if ($values['parent']) {
			$category->setParent($this->em->find('Category', $values['parent']));
		} else {
			$category->setParent(NULL);
		}

		$category->setName($values['name']);
		$category->setUrl($values['url']);
	}

	public function renderDelete($id)
	{
		$this->template->category = $this->findCategory($id);
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
		$category = $this->findCategory($this->getParameter('id'));
		$this->em->remove($category);
		$this->em->flush();

		$this->flashMessage('Category deleted');
		$this->redirect('default');
	}

}
