<?php

namespace AdminModule;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \BasePresenter
{

	protected function startup()
	{
		parent::startup();
		if (!$this->user->loggedIn &&
			($this->name !== 'Admin:Homepage' || $this->action !== 'loginForm')) {
			$this->redirect('Homepage:loginForm');
		}
	}

}
