<?php
/**
 * @package WP-FASE
 *
 * This file is part of the WP-FASE, The WordPress Filter and Action Syntax Extractor
 *
 * https://github.com/crowdfavorite/WP-FASE
 *
 * Copyright 2013-2014 Crowd Favorite, Ltd. and Chris Mospaw
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use 
 * this file except in compliance with the License. You may obtain a copy of the 
 * license at:
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed 
 * under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS 
 * OF ANY KIND, either express or implied. See the License for the specific language 
 * governing permissions and limitations under the License.
 *
 * See LICENSE.txt
 */

/**
 * HTML output of raw data
 */
class output_html extends fase_wp_reports {

	public function __construct($parser) {
		$this->parser = $parser;
	}

	function assemble() {

		$this->output .= "<!doctype html>\n<html>\n<head>\n\t<title>WP-FASE output</title>\n";
		$this->output .= "\n<!--\n\tThis file was generated by WP-FASE, The WordPress Filter and Action Syntax Extractor";
		$this->output .= "\n\t-- https://github.com/crowdfavorite/WP-FASE\n-->\n\n";
		$this->output .= $this->addCSS();
		$this->output .= "\n</head>\n<body>\n";
		$this->output .= "<h1>Quick links</h1>\n";
		$this->output .= "<ul>\n";
		$this->output .= "\t<li><a href='#header_add_action'>add_action</a></li>\n";
		$this->output .= "\t<li><a href='#header_add_filter'>add_filter</a></li>\n";
		$this->output .= "\t<li><a href='#header_apply_filters'>apply_filters</a></li>\n";
		$this->output .= "\t<li><a href='#header_do_action'>do_action</a>";
		$this->output .= "\t<li><a href='#header_do_action_ref_array'>do_action_ref_array</a></li>\n";
		$this->output .= "\t<li><a href='#header_files'>files</a></li>\n";
		$this->output .= "</ul>\n";

		// Assemble by call type (add_filter, do_action, etc.)
		ksort($this->parser->processed_finds);
		foreach ($this->parser->processed_finds as $type => $finds) {
			$this->output .= "\t<a name='header_$type'></a><h1>$type</h1>\n\n";
	
			ksort ($finds);
			foreach ($finds as $find_name => $instances) {
				$this->output .= "\t<a name='action" . str_replace(array('"', "'"), '', strip_tags($type) . strip_tags($find_name)) . "'></a><h2>$find_name</h2>\n\n";
	
				foreach ($instances as $instance) {
					$this->output .= "\t\t\t<h3>Called in ";
					$this->output .= "<a href='#" . str_replace(array('"', "'"), '', $instance['file']['friendly_name'] . $instance['token'][2]) . "'>'";
					$this->output .= htmlspecialchars($instance['file']['friendly_name']) . "' (" . htmlspecialchars($instance['token'][2]) . ")</a></h3>\n";
					
					$this->output .= "\t\t\t<blockquote>\n";
					if (isset($instance['value_modified'])) {
						$this->output .= "\t\t\t<p><b>Value Modified:</b> ". htmlspecialchars($instance['value_modified']) . "</p>\n";
					}
	
					if (isset($instance['optional_vars']) && count($instance['optional_vars']) > 0) {
						$this->output .= "\t\t\t<p><b>Parameters:</b>\n\t\t\t<ul>\n";
						foreach ($instance['optional_vars'] as $var_name) {
							$this->output .= "\t\t\t\t<li>" . htmlspecialchars($var_name) . "</li>\n";
						}
						$this->output .= "\t\t\t</ul>\n";
					}
	
					if (isset($instance['function_to_add'])) {
						$this->output .= "\t\t\t<p><b>Function Called:</b> ". htmlspecialchars($instance['function_to_add']) . "</p>\n";
					}

					if (isset($instance['priority'])) {
						$this->output .= "\t\t\t<p><b>Priority:</b> ". htmlspecialchars($instance['priority']) . "</p>\n";
					}

					if (isset($instance['arguments'])) {
						$this->output .= "\t\t\t<p><b>Argument Count:</b> ". htmlspecialchars($instance['arguments']) . "</p>\n";
					}

					if (isset($instance['data']['docblock'])) {
						
						$docblock = $instance['data']['docblock'];
	
						// Get rid of actual comment markup
						$docblock = str_replace('/**', '', $docblock);
						$docblock = str_replace('*/', '', $docblock);
						$docblock = preg_replace( '/^\s*\*/m', '', $docblock);
	
						$this->output .= "\n<pre>\n";
						$this->output .= htmlspecialchars($docblock);
						$this->output .= "\n</pre>\n";
					}
					$this->output .= "\t\t\t</blockquote>\n";
				}
			}
		}

		// Assemble by file
		$this->output .= "\t<a name='header_files'></a><h1>List of actions by file</h1>\n\n";
		ksort($this->parser->processed_files);
		foreach ($this->parser->processed_files as $file => $lines) {
			$this->output .= "\t<h1>$file</h1>\n\n";

			ksort ($lines);
			foreach ($lines as $line_number => $instance) {
				$this->output .= "\t\t<a name='" . str_replace(array('"', "'"), '', strip_tags($file) . strip_tags($line_number)) . "'></a>";
				$this->output .= "\t\t<h3>$line_number: ";
				$this->output .= "<a href='#action" . str_replace(array('"', "'"), '', strip_tags($instance['type']) . strip_tags($instance['hook'])) . "'>";
				$this->output .= $instance['type'] . ": '" . $instance['hook'] . "'</a>";
				$this->output .= "</h3>\n";

				$this->output .= "\t\t\t<blockquote>\n";

				if (isset($instance['value_modified'])) {
					$this->output .= "\t\t\t<p><b>Value Modified:</b> ". htmlspecialchars($instance['value_modified']) . "</p>\n";
				}

				if (isset($instance['optional_vars']) && count($instance['optional_vars']) > 0) {
					$this->output .= "\t\t\t<p><b>Parameters:</b>\n\t\t\t<ul>\n";
					foreach ($instance['optional_vars'] as $var_name) {
						$this->output .= "\t\t\t\t<li>" . htmlspecialchars($var_name) . "</li>\n";
					}
					$this->output .= "\t\t\t</ul>\n";
				}

				if (isset($instance['function_to_add'])) {
					$this->output .= "\t\t\t<p><b>Function Called:</b> ". htmlspecialchars($instance['function_to_add']) . "</p>\n";
				}

				if (isset($instance['priority'])) {
					$this->output .= "\t\t\t<p><b>Priority:</b> ". htmlspecialchars($instance['priority']) . "</p>\n";
				}

				if (isset($instance['arguments'])) {
					$this->output .= "\t\t\t<p><b>Argument Count:</b> ". htmlspecialchars($instance['arguments']) . "</p>\n";
				}

				if (isset($instance['data']['docblock'])) {
					
					$docblock = $instance['data']['docblock'];

					// Get rid of actual comment markup
					$docblock = str_replace('/**', '', $docblock);
					$docblock = str_replace('*/', '', $docblock);
					$docblock = preg_replace( '/^\s*\*/m', '', $docblock);

					$this->output .= "\n<pre>\n";
					$this->output .= htmlspecialchars($docblock);
					$this->output .= "\n</pre>\n";
				}
				$this->output .= "\t\t\t</blockquote>\n";
			}
		}

		$this->output .= "\n</body>\n</html>\n";

		return;
	}

	/**
	 * Add basic CSS to the output
	 *
	 * TODO: add ability to override with a CSS file.
	 */ 
	protected function addCSS() {

		$output = '
<style>

	/* Reset taken from: http://meyerweb.com/eric/tools/css/reset/ and modified */
	html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, 
	big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, center, 
	dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, 
	canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, 
	time, mark, audio, video { 
		margin: 0; padding: 0; border: 0; font-size: 100%; font: inherit; 
		font-family: "Source Sans Pro", Arial, Helvetica, sans-serif; vertical-align: baseline; text-align: left; color: #111;
	}
	/* HTML5 display-role reset for older browsers */
	article, aside, details, figcaption, figure, footer, header, hgroup, menu, nav, section { display: block; }

	body { line-height: 1.3; margin: 1.2em; background: #f8f8f8; }

	ol, ul { list-style: square; }
	
	blockquote, q { quotes: none; }
	
	blockquote:before, blockquote:after, q:before, q:after { content: ""; content: none; }
	
	table { border-collapse: collapse; border-spacing: 0; }
	
	h1, h2, h3, h4, h5, h6 { font-weight: bold; margin-bottom: .75em; }
	h1 { font-size: 150%; }
	h2 { font-size: 125%; margin-top: 2em; }
	h3 { font-size: 115%; margin-left: 2.5em; }
	h4 { font-size: 110%; }
	h5, h6 { font-size: 100%; }

	pre { font-family: "Source Code Pro", Concolas, "Courier New", Courier, monospace; }

	p, pre, ul, ol { margin-bottom: 1em; }

	pre { background: #e0e0e0; border: 1px solid #111; padding: 0.8em; }

	li { margin-left: 2.5em; margin-bottom: 0.5em; }

	a { text-decoration: none; }
	a, a:visited { color: #009; }
	a:active, a:hover { color #900; }

	b { font-weight: bold; }

	blockquote { margin-left: 5em; }

</style>';

		return $output;
	}

}