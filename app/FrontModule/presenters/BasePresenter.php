<?php

namespace FrontModule;

/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends \BasePresenter
{

	protected function beforeRender()
	{
		parent::beforeRender();
		$this->template->categories = $this->em->getRepository('Category')
			->findBy(array('parent' => NULL));
	}

}
