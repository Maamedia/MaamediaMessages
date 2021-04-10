<?php

use MediaWiki\MediaWikiServices;

class SpecialMaamediaSurvey extends FormSpecialPage {
	private $dbw;
	private $row;

	public function __construct() {
		parent::__construct( 'MaamediaSurvey' );
		$this->dbw = wfGetDB( DB_MASTER, [], 'survey' );
	}

        public function execute( $par ) {
                $out = $this->getOutput();
                $this->setParameter( $par );
                $this->setHeaders();

		$this->row = $this->dbw->selectRow(
			'survey',
			'*',
			[
				's_id' => md5( $this->getUser()->getName() )
			]
		);

		if ( !$this->row ) {
			$this->dbw->insert(
				'survey',
				[
					's_id' => md5( $this->getUser()->getName() ),
					's_state' => 'viewed'
				]
			);
		}

		$out->addWikiMsg( 'maamedia-survey-header' );

                $form = $this->getForm();
                if ( $form->show() ) {
                        $this->onSuccess();
                }
        }

	protected function getFormFields() {
		global $wgWikiCategories;

		$this->getOutput()->addModules( 'ext.wiki.oouiform' );
		$this->getOutput()->addJsConfigVars( 'wgWikiOOUIFormTabs', [] );

		$dbRow = json_decode( $this->row->s_data ?? '[]', true );

		$categoryOptions = $wgWikiCategories;
		unset( $categoryOptions[array_search( 'uncategorised', $categoryOptions )] );

		$yesNoOptions = [
			$this->msg( 'maamedia-survey-yes' )->text() => 1,
			$this->msg( 'maamedia-survey-no' )->text() => 0
		];

		$accessOptions = [
			$this->msg( 'maamedia-survey-access-sd' )->text() => 'severaldaily',
			$this->msg( 'maamedia-survey-access-d' )->text() => 'daily',
			$this->msg( 'maamedia-survey-access-sw' )->text() => 'severalweekly',
			$this->msg( 'maamedia-survey-access-w' )->text() => 'weekly',
			$this->msg( 'maamedia-survey-access-sm' )->text() => 'severalmonthly',
			$this->msg( 'maamedia-survey-access-m' )->text() => 'monthly',
			$this->msg( 'maamedia-survey-access-lm' )->text() => 'lessmothly',
			$this->msg( 'maamedia-survey-access-ft' )->text() => 'firsttime'
		];

		$formDescriptor = [
			'q1' => [
				'type' => 'select',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q1',
				'options' => [
					$this->msg( 'maamedia-survey-q1-anon-read' )->text() => 'anon-read',
					$this->msg( 'maamedia-survey-q1-anon-edit' )->text() => 'anon-edit',
					$this->msg( 'maamedia-survey-q1-account-read' )->text() => 'account-read',
					$this->msg( 'maamedia-survey-q1-account-edit' )->text() => 'account-edit',
					$this->msg( 'maamedia-survey-q1-account-manage' )->text() => 'account-manage'
				],
				'default' => $dbRow['q1'] ?? false
			],
			'q2' => [
				'type' => 'text',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q2',
				'default' => $dbRow['q2'] ?? false,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-read' ], [ '===', 'wpq1', 'anon-edit' ] ]
			],
			'q3a' => [
				'type' => 'select',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q3a',
				'options' => $accessOptions,
				'default' => $dbRow['q3a'] ?? false,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-read' ], [ '===', 'wpq1', 'account-read' ] ]
			],
			'q3b' => [
				'type' => 'select',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q3b',
				'options' => $accessOptions,
				'default' => $dbRow['3b'] ?? false,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-edit' ], [ '===', 'wpq1', 'account-edit' ], [ '===', 'wpq1', 'account-manage' ] ]
			],
			'q4a' => [
				'type' => 'select',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q4a',
				'options' => $categoryOptions,
				'default' => $dbRow['q4a'] ?? false,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-read' ], [ '===', 'wpq1', 'account-read' ] ]
			],
			'q4b' => [
				'type' => 'select',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q4b',
				'options' => $categoryOptions,
				'default' => $dbRow['q4b'] ?? false,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-edit' ], [ '===', 'wpq1', 'account-edit' ], [ '===', 'wpq1', 'account-manage' ] ]
			],
			'q5a' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q5a',
				'default' => $dbRow['q5a'] ?? 0,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-read' ], [ '===', 'wpq1', 'account-read' ] ]
			],
			'q5b' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q5b',
				'default' => $dbRow['q5b'] ?? 0,
				'hide-if' => [ 'NOR',  [ '===', 'wpq1', 'anon-edit' ], [ '===', 'wpq1', 'account-edit' ], [ '===', 'wpq1', 'account-manage' ] ]
			],
			'skin' => [
				'type' => 'hidden',
				'default' => MediaWikiServices::getInstance()->getUserOptionsLookup()->getOption( $this->getUser(), 'skin', 'vector' )
			],
			'q6' => [
				'type' => 'radio',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q6',
				'options' => $yesNoOptions,
				'default' => $dbRow['q6'] ?? false
			],
			'q6-1' => [
				'type' => 'text',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q6-1',
				'default' => $dbRow['q6-1'] ?? false,
				'hide-if' => [ '===', 'wpq6', '1' ]
			],
			'q7' => [
				'type' => 'info',
				'cssclass' => 'wiki-infuse',
				'default' => $this->msg( 'maamedia-survey-q7' )->text()
			],
			'q7-ci' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-ci',
				'default' => $dbRow['q7-ci'] ?? false
			],
			'q7-si' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-si',
				'default' => $dbRow['q7-si'] ?? false
			],
			'q7-up' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-up',
				'default' => $dbRow['q7-up'] ?? false
			],
			'q7-speed'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-speed',
				'default' => $dbRow['q7-speed'] ?? false
			],
			'q7-oe'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-oe',
				'default' => $dbRow['q7-oe'] ?? false
			],
			'q7-wc' => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-wc',
				'default' => $dbRow['q7-wc'] ?? false,
				'hide-if' => [ '!==', 'wpq1', 'account-manage' ]
			],
			'q7-tasks'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q7-tasks',
				'default' => $dbRow['q7-tasks'] ?? false,
				'hide-if' => [ '!==', 'wpq1', 'account-manage' ]
			],
			'q8' => [
				'type' => 'radio',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q8',
				'options' => $yesNoOptions,
				'default' => $dbRow['q8'] ?? 0,
				'hide-if' => [ '!==', 'wpq1', 'account-manage' ]
			],
			'q8-1' => [
				'type' => 'info',
				'cssclass' => 'wiki-infuse',
				'default' => $this->msg( 'maamedia-survey-q7' )->text(),
				'hide-if' => [ '!==', 'wpq8', '1' ]
			],
			'q8-e'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q8-e',
				'default' => $dbRow['q8-e'] ?? false,
				'hide-if' => [ '!==', 'wpq8', '1' ]
			],
			'q8-f'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q8-f',
				'default' => $dbRow['q8-f'] ?? false,
				'hide-if' => [ '!==', 'wpq8', '1' ]
			],
			'q8-c'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q8-c',
				'default' => $dbRow['q8-c'] ?? false,
				'hide-if' => [ '!==', 'wpq8', '1' ]
			],
			'q8-u'  => [
				'type' => 'int',
				'cssclass' => 'wiki-infuse',
				'min' => 1,
				'max' => 5,
				'label-message' => 'maamedia-survey-q8-u',
				'default' => $dbRow['q8-u'] ?? false,
				'hide-if' => [ '!==', 'wpq8', '1' ]
			],
			'q9' => [
				'type' => 'text',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q9',
				'default' => $dbRow['q9'] ?? false,
				'hide-if' => [ '!==', 'wpq1', 'account-manage' ]
			],
			'q11' => [
				'type' => 'info',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11'
			],
			'q11-d' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-d',
				'default' => $dbRow['q11-d'] ?? false
			],
			'q11-s' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-s',
				'default' => $dbRow['q11-s'] ?? false
			],
			'q11-v' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-v',
				'default' => $dbRow['q11-v'] ?? false
			],
			'q11-p' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-p',
				'default' => $dbRow['q11-p'] ?? false
			],
			'q11-f' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-f',
				'default' => $dbRow['q11-f'] ?? false
			],
			'q11-c' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-c',
				'default' => $dbRow['q11-c'] ?? false
			],
			'q11-cd' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-cd',
				'default' => $dbRow['q11-cd'] ?? false
			],
			'q11-ai' => [
				'type' => 'check',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q11-ai',
				'default' => $dbRow['q11-ai'] ?? false
			],
			'q12' => [
				'type' => 'textarea',
				'cssclass' => 'wiki-infuse',
				'rows' => 3,
				'label-message' => 'maamedia-survey-q12',
				'default' => $dbRow['q12'] ?? false
			],
			'q13' => [
				'type' => 'textarea',
				'cssclass' => 'wiki-infuse',
				'rows' => 3,
				'label-message' => 'maamedia-survey-q13',
				'default' => $dbRow['q13'] ?? false
			],
			'q14' => [
				'type' => 'textarea',
				'cssclass' => 'wiki-infuse',
				'rows' => 3,
				'label-message' => 'maamedia-survey-q14',
				'default' => $dbRow['q14'] ?? false
			],
			'contact' => [
				'type' => 'radio',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q15',
				'options' => $yesNoOptions,
				'default' => $dbRow['contact'] ?? 0
			]
		];

		if ( $this->getUser()->canReceiveEmail() ) {
			$formDescriptor['email'] = [
				'type' => 'hidden',
				'default' => $this->getUser()->getEmail()
			];
		} else {
			$formDescriptor['email'] = [
				'type' => 'email',
				'cssclass' => 'wiki-infuse',
				'label-message' => 'maamedia-survey-q15-1',
				'default' => $row->s_email ?? '',
				'hide-if' => [ '===', 'wpcontact', '0' ]
			];
		}

		return $formDescriptor;
	}

	public function onSubmit( array $formData ) {
		$email = $formData['email'];
		unset( $formData['email'] );

		$rows = [
			's_state' => 'completed',
			's_data' => json_encode( $formData ),
			's_email' => $email
		];

		$this->dbw->update(
			'survey',
			$rows,
			[
				's_id' => md5( $this->getUser()->getName() )
			]
		);

		$this->getOutput()->addHTML( '<div class="successbox">' . $this->msg( 'maamedia-survey-done' )->escaped() . '</div>' );

		return true;
	}

	protected function getDisplayFormat() {
		return 'ooui';
	}
}
