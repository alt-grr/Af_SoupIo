<?php

class Af_SoupIo extends Plugin {

	private $host;

	function about() {
		return array(1.0,
			"Insert larger images in soup.io feeds.",
			"kuc");
	}

	function api_version() {
		return 2;
	}

	function init($host) {
		$this->host = $host;
		$host->add_hook($host::HOOK_ARTICLE_FILTER, $this);
	}

	function hook_article_filter($article) {
		$owner_uid = $article["owner_uid"];

		if (strpos($article["link"], "soup.io") !== FALSE) {
			if (strpos($article["plugin_data"], "soupio,$owner_uid:") === FALSE) {

				$doc = new DOMDocument();
				@$doc->loadHTML('<?xml encoding="UTF-8"?>' . $article["content"]);

				if ($doc) {
					$xpath = new DOMXPath($doc);
					$entries = $xpath->query('(//img[@alt])');

					$basenode = false;

					foreach ($entries as $entry) {
						// mark that image was found
						$basenode = $entry->parentNode;

						// remove width and height
						$width = $entry->getAttributeNode("width");
						if ($width) {
							$entry->removeAttributeNode($width);
						}
						$height = $entry->getAttributeNode("height");
						if ($height) {
							$entry->removeAttributeNode($height);
						}

						// replace src
						$src = $entry->getAttribute("src");
						$src = str_replace('_400.', '.', $src);
						$entry->setAttribute("src", $src);
						break;
					}

					if($basenode) {
						$doc->removeChild($doc->firstChild);
						$article["content"] = $doc->saveHTML();
						$article["plugin_data"] = "soupio,$owner_uid:" . $article["plugin_data"];
					}
				}
			} else if (isset($article["stored"]["content"])) {
				$article["content"] = $article["stored"]["content"];
			}
		}

		return $article;
	}
}
