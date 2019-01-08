<?php

namespace ColdTrick\HTMLEmailHandler\Parts;

use Zend\Mime\Part;
use Zend\Mime\Mime;

class HtmlPart extends Part {
	
	public function __construct($content = '') {
		parent::__construct($content);
		
		$this->setType(Mime::TYPE_HTML);
		$this->setCharset('"utf-8"');
// 		$this->setId('html');
	}
}
