<?php
	
abstract class Resourcer_Abstract_XML extends Resourcer_Abstract_Common {
	
	protected function processData( $instance ) {
		
		$files = $this->getFiles( $instance );
		
		$newXml = new SimpleXMLElement("<{$this->type}></{$this->type}>");
		foreach( $files as $file ) {
			$xml = simplexml_load_file( $file );
			$this->_mergeXML( $newXml, $xml	);
		}
		if( method_exists( $this, 'postprocess' ) ) {
			$this->postprocess( $newXml, $instance );
		}
		return $newXml->asXML();
	}
	
	private function _mergeXML( &$xml1, &$xml2 ) {
		
		foreach( $xml2 as $name => $node ) {
			if( property_exists( $xml1, $name ) ) {
				foreach( $node->attributes() as $key => $value ) {
					$xml1->$name->addAttribute( $key, $value );
				}
				$this->_mergeXML( $xml1->$name, $node );
			} else {
				$new = $xml1->addChild( $name, $node );
				foreach( $node->attributes() as $key => $value ) {
					$new->addAttribute( $key, $value );
				}
				$this->_mergeXML( $new, $node );
			}
		}
	}
			
}
