<?php

namespace AdminModule;

use Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{

	public function actionLogout()
	{
		$this->user->logout();
		$this->redirect('Homepage:loginForm');
	}

	protected function createComponentLoginForm()
	{
		$form = new Form();
		$form->addText('username', 'Username')
			->setRequired('Username is required');
		$form->addText('password', 'Password')
			->setRequired('Password is required');
		$form->addSubmit('submit', 'Login');
		$form->onSuccess[] = array($this, 'submitLoginForm');

		return $form;
	}

	public function submitLoginForm(Form $form)
	{
		try {
			$values = $form->getValues();
			$this->user->setExpiration('+ 30 minutes');
			$this->user->login($values['username'], $values['password']);
			$this->flashMessage('You are successfully logged in!');
			$this->redirect('default');
		} catch (\Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

}
