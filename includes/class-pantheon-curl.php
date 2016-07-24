<?php
if ( ! class_exists( 'PantheonCurl' ) ):

	/**
	 * @file
	 * Override Solarium so that more options can be set before executing curl.
	 */
	use Solarium\Core\Client\Adapter\Curl;

	class PantheonCurl extends Curl {
		/**
		 * {@inheritdoc}
		 */
		public function createHandle( $request, $endpoint ) {
			$handler = parent::createHandle( $request, $endpoint );
			if ( defined( 'PANTHEON_ENVIRONMENT' ) ) {
				curl_setopt( $handler, CURLOPT_SSL_VERIFYPEER, false );
				$client_cert = $_SERVER['HOME'] . '/certs/binding.pem';
				curl_setopt( $handler, CURLOPT_SSLCERT, $client_cert );
			}

			return $handler;
		}
	}

endif;