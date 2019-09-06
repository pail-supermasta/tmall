<?php

/**
 * multi country price configuration
 * @author auto create
 */
class MultiCountryPriceConfigurationDto
{
	
	/** 
	 * Price list for different countries
	 **/
	public $country_price_list;
	
	/** 
	 * Currently supporting absolute/relative/percentage. Please test carefully before uploading products.
	 **/
	public $price_type;	
}
?>