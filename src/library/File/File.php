<?php

	namespace Helo\Library\File;

	class File {
		
		static public $mime_type = array(
			// application
			'json' 		 => 'application/json',
			'javascript' => 'application/javascript',
			'ogg' 		 => 'application/ogg',
			'pdf' 		 => 'application/pdf',
			'postscript' => 'application/postscript',
			'rdf'		 => 'application/rdf+xm',
			'rss' 		 => 'application/rss+xml',
			'soap'		 => 'application/soap+xml',
			'woff'		 => 'application/font-woff',
			'xhtml' 	 => 'application/xhtml+xml',
			'xml' 		 => 'application/xml',
			'dtd' 		 => 'application/xml-dtd',
			'xop'		 => 'application/xop+xml',
			'zip' 		 => 'application/zip',

			// Image
			'gif' 		 => 'image/gif',
			'jpg' 		 => 'image/jpeg',
			'jpeg' 		 => 'image/jpeg',
			'pjpeg' 	 => 'image/pjpeg',
			'png' 	 	 => 'image/png',
			'svg' 	 	 => 'image/svg+xml',
			'tiff' 	 	 => 'image/tiff',

			// Text
			'cmd' 	 	 => 'text/cmd',
			'css' 	 	 => 'text/css',
			'csv' 	 	 => 'text/csv',
			'html' 	 	 => 'text/html',
			'plain' 	 => 'text/plain',
			'vcard' 	 => 'text/vcard',
			'xml' 	 	 => 'text/xml',

			// Audio
			'mp3' 	 	 => 'audio/mp3',
			'ogg' 	 	 => 'audio/ogg',

			// Video
			'mpeg' 	 	 => 'video/mpeg',
			'mp4' 	 	 => 'video/mp4',
			'ogg' 	 	 => 'video/ogg',
			'quicktime'  => 'video/quicktime',
			'webm' 	 	 => 'video/webm',
			'matroska' 	 => 'video/x-matroska',
			'wmv' 	 	 => 'video/x-ms-wmv',
			'flv' 	 	 => 'video/x-flv',
		);

		static public function get_mime_type($type){
			if (isset(self::$mime_type[$type])) {
				$mime = self::$mime_type[$type];
			} else  {
				$mime = self::$mime_type['plain'];
			}
			
			return $mime;
		}
	}
?>