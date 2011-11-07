<?php
/**
 * Converts [aaaa:xxxxxxx] into links
 *
 * Syntax: [aaaa:#######] - will be replaced with a specified link match
 * 
 * @license    Beerware x 3
 * @author     Charles Timko <charles@pushesp.com>
 **/
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 **/
class syntax_plugin_linker extends DokuWiki_Syntax_Plugin
{
	// Hashmap of the keys and replacements
	private $map = array();

	/**
	 * Based on the role of inheritance, let's go ahead and make our
	 * constructor...I am not sure if this will break anything...it
	 * could possibly break EVERYTHING.
	 **/
	function __construct()
	{
		$cfg = dirname(__FILE__).'/plugin.cfg';

		// Open the config file and read in everything
		$fr = fopen($cfg, 'r');

		// Parse the data into the array
		while(!feof($fr))
		{
			$s = fgets($fr);
			$pieces = explode(':', $s, 2);
			$this->map[$pieces[0]] = $pieces[1];
		}

		// Close the config file
		fclose($fr);

		// For sanity, call the parent constructor now
		//parent::__construct(); -- No worky yet...later maybe
	}

	/**
	 * getInfo is called by the Doku Engine in the Admin Panel to show
	 * all configuration information. It is good stuff
	 **/
	function getInfo()
	{
		return array(
				'author' => 'Charles Timko',
				'email'  => 'charles@pushesp.com',
				'date'   => '2011-11-07',
				'name'   => 'Linker Plugin',
				'desc'   => 'Turns specially crafted tags into links',
				'url'    => 'n/a',
			    );
	}

	/**
	 * getType returns to Doku Engine the specifc type of plugin,
	 * in our case, substitution, we are replacing things
	 **/
	function getType()
	{
		return 'substition';
	}

	/**
	 * getSort() tells the Doku Engine when to call our plugin...Since the
	 * formatting I am using is similar to the the normal URL parsing, we will
	 * do it after that
	 **/
	function getSort()
	{
		return 365;
	}

	/**
	 * connectTo() is used to register our patterns with the Doku Engine
	 **/
	function connectTo($mode)
	{
		$this->Lexer->addSpecialPattern('\[[[:alnum:]]+:[^\|]+\|?[^\]]*\]',$mode,'plugin_linker');
	}

	/**
	 * This is handles all states when the lexer is matching
	 * 	DOKU_LEXER_ENTER - Match entrance is found [addEnterPattern]
	 * 	DOKU_LEXER_EXIT - Match exit pattern is found [addExitPattern]
	 * 	DOKU_LEXER_MATCHED - Match pattern is found [addMatchedPattern]
	 * 	DOKU_LEXER_UNMATCHED - Unmatched pattern [addUnmatchedPattern] NO IDEA WHY YOU WOULD USE THIS
	 * 	DOKU_LEXER_SPECIAL - Match special pattern [addSpecialPattern]
	 **/
	function handle($match, $state, $pos, &$handler)
	{
		switch ($state)
		{
			case DOKU_LEXER_ENTER : 
				break;
			case DOKU_LEXER_MATCHED :
				break;
			case DOKU_LEXER_UNMATCHED :
				break;
			case DOKU_LEXER_EXIT :
				break;
			case DOKU_LEXER_SPECIAL :
				list($type, $rest) = preg_split("/:/", substr($match, 1, -1), 2);
				list($quant, $title) = preg_split("/\|/", $rest, 2);
				return array($state, array($type, array($quant, $title)));
		}
		return array();
	}

	/**
	 * Actions to take place when a match has been found (depending on the
	 * state, i.e. in our case DOKU_LEXER_SPECIAL...
	 **/
	function render($mode, &$renderer, $data)
	{
		if($mode == 'xhtml')
		{
			list($state, $type_match) = $data;
			switch($state)
			{
				case DOKU_LEXER_SPECIAL:
					list($type, $match) = $type_match;
					if(!array_key_exists($type, $this->map)) return false;
					list($quant, $title) = $match;
					if($title === null) $title = $type.':'.$quant;
					$renderer->doc .= '<a class="urlextern" href="'.preg_replace('/#X#/', $quant, $this->map[$type]).'" target="_blank">'.$title.'</a>';
					break;
			}
			return true;
		}
		return false;
	}
}
?>
