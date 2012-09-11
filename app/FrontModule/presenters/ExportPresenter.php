<?php

namespace FrontModule;

class ExportPresenter extends BasePresenter
{

	public function renderDefault()
	{
		$this->template->products = $this->em->getRepository('Product')->findBy(array(
			'visible' => TRUE,
		));

		$this->getHttpResponse()->setContentType('text/xml');
	}

}
