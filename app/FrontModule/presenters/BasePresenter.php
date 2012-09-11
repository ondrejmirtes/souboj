<?php

namespace FrontModule;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \BasePresenter
{

	private $em;

	protected function startup()
	{
		parent::startup();
		$this->em = $this->context->em;
	}

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->categories = $this->em->getRepository('Category')
			->findBy(array('parent' => NULL));
	}

}
