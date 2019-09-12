<?php

/**
 * Price list for different countries
 * @author auto create
 */
class SingleCountryPriceDto
{
	
	/** 
	 * Currently supporting RU US CA ES FR UK NL IL BR CL AU UA BY JP TH SG KR ID MY PH VN IT DE SA AE PL TR
	 **/
	public $ship_to_country;
	
	/** 
	 * Sku price list under the same ship_to_country
	 **/
	public $sku_price_by_country_list;	
}
?>