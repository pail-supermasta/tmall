<?php

/**
 * Sku price list under the same ship_to_country
 * @author auto create
 */
class SingleSkuPriceByCountryDto
{
	
	/** 
	 * sku_code, the same as the sku_code in sku_info_list
	 **/
	public $sku_code;
	
	/** 
	 * Value of price configuration, which represents different meanings according to different price_type
	 **/
	public $value;	
}
?>