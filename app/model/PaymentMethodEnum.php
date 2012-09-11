<?php

class PaymentMethodEnum
{

	const CASH_SHOP = 1;

	const CASH_COD = 2;

	const ACCOUNT_SHOP = 3;

	const ACCOUNT_POSTOFFICE = 4;

	const CARD_SHOP = 5;

	const CARD_POSTOFFICE = 6;

	public static function getValues()
	{
		return array(
			self::CASH_SHOP => _('Cash, shop'),
			self::CASH_COD => _('Cash, on delivery'),
			self::ACCOUNT_SHOP => _('Paid to account, shop'),
			self::ACCOUNT_POSTOFFICE => _('Paid to account, post office'),
			self::CARD_SHOP => _('Credit card, shop'),
			self::CARD_POSTOFFICE => _('Credit card, post office'),
		);
	}

}