<?php

namespace ColdTrick\HTMLEmailHandler\Parts;

use Zend\Mime\Part;
use Zend\Mime\Mime;

class HtmlPart extends Part {
	
	public function __construct($content = '') {
		parent::__construct($content);
		
		$this->setType(Mime::TYPE_HTML);
		$this->setCharset('utf-8');
		$this->setEncoding(Mime::ENCODING_BASE64);
		$this->setId('htmltext');
	}
}
