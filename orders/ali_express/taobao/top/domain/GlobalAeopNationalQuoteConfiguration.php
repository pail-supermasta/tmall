<?php

/**
 * Sub-national pricing rules datas,suggest using new format,please refer to "API change in Mar 08, 2019": https://developers.aliexpress.com/en/doc.htm?docId=109105&docType=1
 * @author auto create
 */
class GlobalAeopNationalQuoteConfiguration
{
	
	/** 
	 * jsonArray format sub-national pricing rules data. 1) The data format that is scaled based on the base price: [{"shiptoCountry": "US", "percentage": "5"}, {"shiptoCountry": "RU", "percentage": "- 2"}] Where shiptoCountry: ISO country code (currently supports 11 countries: RU, US, CA, ES, FR, UK, NL, IL, BR, CL, AU), percentage: price adjustment ratio relative to base price Integer, support negative, current limit> = - 30 && <= 100)
	 **/
	public $configuration_data;
	
	/** 
	 * Sub-national pricing rules type [percentage: based on the benchmark price in proportion to the configuration]
	 **/
	public $configuration_type;	
}
?>