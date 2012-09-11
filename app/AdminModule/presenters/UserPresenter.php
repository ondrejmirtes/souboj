<?php

namespace AdminModule;

use Nette\Application\UI\Form;
use Nette\Utils\Strings;

class UserPresenter extends BasePresenter
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
		$this->template->users = $this->em->getRepository('User')->findAll();
	}

	protected function createComponentAddForm()
	{
		$form = new Form();
		$em = $this->em;
		$that = $this;

		$form->addText('username', 'Username')
			->setRequired('Username is required')
			->addRule(function($control) use ($em, $that) {
				$value = $control->value;
				$user = $em->getRepository('User')->findOneByUsername($value);
				return $user === NULL
					|| $user->id == $that->getParameter('id');
			}, 'Username already exists');

		$password1 = $form->addPassword('password', 'Password');
		if ($that->getParameter('id') === NULL) {
			$password1->setRequired('Fill in the password');
		}
		$password2 = $form->addPassword('password2', 'Password (verify)')
			->addConditionOn($password1, Form::FILLED)
			->addRule(Form::FILLED, 'Fill in the password again');

		$password2->addCondition(Form::FILLED)
			->addRule(Form::EQUAL, 'Passwords do not match', $password1);

		$form->addCheckbox('admin', 'Admin');

		$form->addSubmit('submit', 'Save');
		$form->onSuccess[] = array($this, 'submitAddForm');

		return $form;
	}

	public function submitAddForm(Form $form)
	{
		$values = $form->getValues();
		$user = new \User;

		$this->fillValues($user, $values);

		$this->em->persist($user);
		$this->em->flush();

		$this->flashMessage('User added.');
		$this->redirect('default');
	}

	public function renderEdit($id)
	{
		$user = $this->findUser($id);
		$this->template->user = $user;

		$this['editForm']->setDefaults(array(
			'username' => $user->username,
			'admin' => $user->admin,
		));
	}

	private function findUser($id)
	{
		$user = $this->em->find('User', (int) $id);
		if ($user === NULL) {
			throw new \Nette\Application\BadRequestException;
		}
		return $user;
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
		$user = $this->findUser($this->getParameter('id'));

		$this->fillValues($user, $values);

		$this->em->flush();

		$this->flashMessage('User updated.');
		$this->redirect('default');
	}

	private function fillValues(\User $user, $values)
	{
		$user->setCredentials($values['username'], $values['password']);
		$user->setAdmin($values['admin']);
	}

	public function renderDelete($id)
	{
		$this->template->user = $this->findUser($id);
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
		$user = $this->findUser($this->getParameter('id'));
		$this->em->remove($user);
		$this->em->flush();

		$this->flashMessage('User deleted');
		$this->redirect('default');
	}

}
