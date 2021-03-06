<?php

if ( ! defined('EXT')) exit('Invalid file request');

/**
 * Scrippet Class
 *
 * @package   FieldFrame
 * @author    Matthew Pennell
 * @copyright Copyright (c) 2009 Matthew Pennell, Nima Yousefi
 * @license   MIT License
 */
class Mgp_scrippet extends Fieldframe_Fieldtype {

    var $info = array(
        'name'             => 'Scrippet',
        'version'          => '1.0.1',
        'desc'             => 'Provides a formatted screenplay field type',
        'docs_url'         => 'http://www.thewatchmakerproject.com/'
    );

    function display_field($field_name, $field_data, $field_settings)
    {
        global $DSP;
        return $DSP->input_textarea($field_name, $field_data, 10);
    }
    
    function display_cell($cell_name, $cell_data, $cell_settings)
    {
        global $DSP;
        return $DSP->input_textarea($cell_name, $cell_data, 5);
    }
    
    function display_tag($params, $tagdata, $field_data, $field_settings)
    {
		$width = ( ! $params['width']) ? '400' :  $params['width'];
		$bg_color = ( ! $params['bg_color']) ? '#FFFFFC' :  $params['bg_color'];
		$text_color = ( ! $params['text_color']) ? '#000000' :  $params['text_color'];
		$alignment = ( ! $params['alignment']) ? 'Left' :  $params['alignment'];
		
		$style = 'width: ' . $width . 'px; background-color: ' . $bg_color . '; color: ' . $text_color . ';';
		if ($alignment == 'center')
		{
			$style .= ' margin: 0 auto 16px auto !important;';
		}

		return '<div class="scrippet" style="' . $style . '">' . $this->scrippetize($field_data) . '</div>';
    }
    
	/*
	Scrippetize v1.2
	
	This is the core scrippet-to-HTML function. Usage is simple: put scrippet containing $text in,
	and fully formatted HTML comes out.
	
	Based upon the Scrippet concept and design by John August (http://johnaugust.com).
	
	-- Released under MIT License--
	Copyright (c) 2008 Nima Yousefi
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in
	all copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
	THE SOFTWARE.
	*/
	function scrippetize($text) {
		// Create arrays & setup some basic character replacements
		$pattern   = array('/\r/', '/&amp;/', '/\.{3}|…/', '/\-{2}|—|–/');
		$replace   = array('', '&', '&#46;&#46;&#46;', '&#45;&#45;');
		
		// Sceneheaders must start with INT, EXT, or EST
		$pattern[] = '/\n(INT|EXT|[^a-zA-Z0-9]EST)([\.\-\s]+?)(.+?)([A-Za-z0-9\)\s\.])\n/';
		$replace[] = '<p class="sceneheader">\1\2\3\4</p>' . "\n";
		
		// Catches transitions
		// Looks for a colon, with some hard coded exceptions that don't use colons.
		$pattern[] = '/\n([^<>\na-z]*?:|FADE TO BLACK\.|FADE OUT\.|CUT TO BLACK\.)[\s]??\n/';
		$replace[] = '<p class="transition">\1</p>' . "\n";
		
		// Catches multi-line action blocks
		// looks for all caps without punctuation, then two Newlines.
		// This differentiates from character cues because Cues will only have a single break, then the dialogue/parenthetical.    
		$pattern[] = '/\n{2}(([^a-z\n\:]+?[\.\?\,\s\!]*?)\n{2}){1,2}/';
		$replace[] = "\n" . '<p class="action">\2</p>' . "\n";
		
		// Catches character cues
		// Looks for all caps, parenthesis (for O.S./V.O.), then a single newline.
		$pattern[] = '/\n([^<>a-z\s][^a-z:\!\?]*?[^a-z\(\!\?:,][\s]??)\n{1}/'; // minor change that makes it work better
		$replace[] = '<p class="character">\1</p>';    
		
		// Catches parentheticals
		// Just looks for text between parenthesis.
		$pattern[] = '/(\([^<>]*?\)[\s]??)\n/';
		$replace[] = '<p class="parenthetical">\1</p>';
		
		// Catches dialogue
		// Must follow a character cue or parenthetical.
		$pattern[] = '/(<p class="character">.*<\/p>|<p class="parenthetical">.*<\/p>)\n{0,1}(.+?)\n/';
		$replace[] = '\1' . "\n" . '<p class="dialogue">\2</p>' . "\n";    
		
		// Defaults.
		$pattern[] = '/([^<>]*?)\n/';
		$replace[] = '<p class="action">\1</p>' . "\n";
		
		// Hack - cleans up the mess the action regex is leaving behind.
		$pattern[] = '/<p class="action">[\n\s]*?<\/p>/';
		$replace[] = "";
		
		// Styling
		$pattern[] = '/(\*{2}|\[b\])(.*?)(\*{2}|\[\/b\])/';
		$replace[] = '<b>\2</b>';
		
		$pattern[] = '/(\*{1}|\[i\])(.*?)(\*{1}|\[\/i\])/';
		$replace[] = '<i>\2</i>';
		
		$pattern[] = '/(_|\[u\])(.*?)(_|\[\/u\])/';
		$replace[] = '<u>\2</u>';	
		
        // Remove any HTML tags in the scrippet block
        $text = preg_replace('/<\/p>|<br(\/)?>/i', "\n", $text);
        $text = strip_tags($text);
        
        $text .= "\n";   // this is a hack to eliminate some weirdness at the end of the scrippet

        // Regular Expression Magic!                        
        $text = preg_replace($pattern, $replace, $text);

		return $text;
	}

}
?>