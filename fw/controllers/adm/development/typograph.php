<?php

/**
 * Class AdmDevelopmentTypographController
 */
class AdmDevelopmentTypographController extends \Difra\Controller {

	public function dispatch() {

		\Difra\View::$instance = 'adm';
	}

	/**
	 * View typograph settings
	 */
	public function indexAction() {

		$mainNode = $this->root->appendChild( $this->xml->createElement( 'typograph' ) );
		\Difra\Libs\Typographer::getSettingsXML( $mainNode );
	}

	/**
	 * Save typograph settings
	 *
	 * @param \Difra\Param\AjaxCheckbox $spaceAfterShortWord
	 * @param \Difra\Param\AjaxInt      $lengthShortWord
	 * @param \Difra\Param\AjaxCheckbox $spaceBeforeLastWord
	 * @param \Difra\Param\AjaxInt      $lengthLastWord
	 * @param \Difra\Param\AjaxCheckbox $spaceAfterNum
	 * @param \Difra\Param\AjaxCheckbox $spaceBeforeParticles
	 * @param \Difra\Param\AjaxCheckbox $delRepeatSpace
	 * @param \Difra\Param\AjaxCheckbox $delSpaceBeforePunctuation
	 * @param \Difra\Param\AjaxCheckbox $delSpaceBeforeProcent
	 * @param \Difra\Param\AjaxCheckbox $doReplaceBefore
	 * @param \Difra\Param\AjaxCheckbox $doReplaceAfter
	 * @param \Difra\Param\AjaxCheckbox $doMacros
	 */
	public function saveAjaxAction( \Difra\Param\AjaxCheckbox $spaceAfterShortWord,
					\Difra\Param\AjaxInt $lengthShortWord,
					\Difra\Param\AjaxCheckbox $spaceBeforeLastWord,
					\Difra\Param\AjaxInt $lengthLastWord,
					\Difra\Param\AjaxCheckbox $spaceAfterNum,
					\Difra\Param\AjaxCheckbox $spaceBeforeParticles,
					\Difra\Param\AjaxCheckbox $delRepeatSpace,
					\Difra\Param\AjaxCheckbox $delSpaceBeforePunctuation,
					\Difra\Param\AjaxCheckbox $delSpaceBeforeProcent,
					\Difra\Param\AjaxCheckbox $doReplaceBefore,
					\Difra\Param\AjaxCheckbox $doReplaceAfter,
					\Difra\Param\AjaxCheckbox $doMacros ) {

		$settingsArray = array( 'spaceAfterShortWord' => $spaceAfterShortWord->val(),
					'lengthShortWord' => $lengthShortWord->val(),
					'spaceBeforeLastWord' => $spaceBeforeLastWord->val(),
					'lengthLastWord' => $lengthLastWord->val(),
					'spaceAfterNum' => $spaceAfterNum->val(),
					'spaceBeforeParticles' => $spaceBeforeParticles->val(),
					'delRepeatSpace' => $delRepeatSpace->val(),
					'delSpaceBeforePunctuation' => $delSpaceBeforePunctuation->val(),
					'delSpaceBeforeProcent' => $delSpaceBeforeProcent->val(),
					'doReplaceBefore' => $doReplaceBefore->val(),
					'doReplaceAfter' => $doReplaceAfter->val(),
					'doMacros' => $doMacros->val() );

		\Difra\Config::getInstance()->set( 'typograph', $settingsArray );
		$this->ajax->notify( \Difra\Locales::getInstance()->getXPath( 'adm/typograph/saved' ) );
	}

}