<?php
	
	abstract class Resourcer_Abstract_XSLT extends Resourcer_Abstract_Common {
		
		protected function processData( $instance ) {
			
			// collect files
			$files = array();
			if( !empty( $this->resources[$instance]['specials'] ) ) {
				foreach( $this->resources[$instance]['specials'] as $resource ) {
					if( !empty( $resource['files'] ) ) {
						$files = array_merge( $files, $resource['files'] );
					}
				}
			}
			if( !empty( $this->resources[$instance]['files'] ) ) {
				$files = array_merge( $files, $this->resources[$instance]['files'] );
			}
			
			// create master template
			$template = 
				'<' . '?xml version="1.0" encoding="UTF-8"?' . '>
				<!DOCTYPE xsl:stylesheet [
					<!ENTITY % lat1 PUBLIC "-//W3C//ENTITIES Latin 1 for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-lat1.ent">
					<!ENTITY % symbol PUBLIC "-//W3C//ENTITIES Symbols for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-symbol.ent">
					<!ENTITY % special PUBLIC "-//W3C//ENTITIES Special for XHTML//EN" "' . DIR_ROOT . 'fw/xslt/xhtml-special.ent">
					%lat1;
					%symbol;
					%special;
				]>
				<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns="http://www.w3.org/1999/xhtml" version="1.0" xmlns:array="none">
				<xsl:output method="xml" indent="yes" encoding="utf-8" omit-xml-declaration="yes" doctype-system="about:legacy-compat"/>
				';
			$templateInner = '';
			foreach( $files as $filename ) {
				$templateInner .= "<xsl:include href=\"$filename\"/>\n";
			}
			if( !Site::getInstance()->devMode ) {
				$templateInner = $this->_extendXSL( $templateInner );
			}
			$template .= $templateInner . '</xsl:stylesheet>';
			return $template;
		}
		
		private function _extendXSL( $text, $path = '/', $depth = 1 ) {
		   
			if( $depth > 10 ) {
				throw new exception( 'Too long XSLT includes recursion depth.' );
			}
			while( true ) {
				preg_match( '/(.*?)<xsl:include href="(.*?)"\/\>(.*)/is', $text, $matches );
				if( empty( $matches ) ) {
					return $text;
				}
				preg_match( '/<xsl\:stylesheet.*?\>(.*)<\/xsl\:stylesheet\>/is',
					file_get_contents( $matches[2]{0} != '/' ? "$path/{$matches[2]}" : $matches[2] ),
					$newMatches );
				if( empty( $newMatches ) ) {
					throw new exception( "Can't find <xsl:stylesheet> section in {$matches[2]}" );
				}
				$text = $matches[1] . $this->_extendXSL( $newMatches[1], dirname( $matches[2] ), $depth + 1 ) . $matches[3];
			}
		}

	}