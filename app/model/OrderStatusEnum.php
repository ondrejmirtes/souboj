<?php

class OrderStatusEnum
{

	const NOT_TAKEN_OVER = 1;

	const TAKEN_OVER_NOT_PAID = 2;

	const TAKEN_OVER_INVOICE_ISSUED = 3;

	const TAKEN_OVER_PAID = 4;

	const CANCELLED = 5;

	const SHIPPED = 6;

	public static function getValues()
	{
		return array(
			self::NOT_TAKEN_OVER => _('Not taken over'),
			self::TAKEN_OVER_NOT_PAID => _('Taken over, not paid'),
			self::TAKEN_OVER_INVOICE_ISSUED => _('Taken over, invoice issued'),
			self::TAKEN_OVER_PAID => _('Taken over, paid'),
			self::CANCELLED => _('Cancelled'),
			self::SHIPPED => _('Shipped'),
		);
	}

}