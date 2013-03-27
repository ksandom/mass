<?php
# Copyright (c) 2012, Kevin Sandom under the BSD License. See LICENSE for full details.

namespace OAuth2;

class MassStore implements DataStore
{
	private $core=null;
	private $name='';
	private $tokenStore='OAuthTokens';
	
	public function __construct(&$core, $name) {
		$this->core=&$core;
		$this->name=$name;
	}

	/**
	*
	* @return \OAuth2\Token
	*/
	public function retrieveAccessToken() {
		$token=$this->core->get('OAuthTokens', $this->name);
		return ($token) ? $token : new Token();
	}

	/**
	* @param \OAuth2\Token $token
	*/
	public function storeAccessToken(Token $token) {
		# TODO Check to see if this needs to be setRef. Ie do we need to get the exact object back later, or a copy good enough?
		$this->core->set('OAuthTokens', $this->name, $token);
	}

	public function  __destruct() {
		$this->core->doUnset($this->tokenStore, $this->name);
	}
}

?>