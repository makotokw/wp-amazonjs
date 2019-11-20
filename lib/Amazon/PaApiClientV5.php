<?php

// phpcs:disable WordPress.Files.FileName
// phpcs:disable WordPress.WP.I18n.NonSingularStringLiteralDomain

class Amazonjs_Amazon_PaApiClientV5 {
	const OPERATION_GET_ITEMS = 'GetItems';
	const OPERATION_SEARCH_ITEMS = 'SearchItems';
	const SEARCH_ITEMS_DEFAULT_ITEM_COUNT = 10;

	protected $access_key_id;
	protected $secret_access_key;
	protected $associate_tag;
	protected $host;
	protected $region;
	protected $text_domain;

	public function __construct( $access_key_id, $secret_access_key, $associate_tag, $base_uri, $region, $text_domain ) {
		$this->access_key_id     = $access_key_id;
		$this->secret_access_key = $secret_access_key;
		$this->associate_tag     = $associate_tag;
		$this->host              = parse_url( $base_uri, PHP_URL_HOST );
		$this->region            = $region;
		$this->text_domain       = $text_domain;
	}


	/**
	 * @param WP_Error $wp_error
	 * @return array
	 */
	protected static function wp_error_to_result( $wp_error ) {
		$error_message = '';
		$errors        = $wp_error->get_error_messages();
		if ( is_array( $errors ) ) {
			$error_message = implode( '<br/>', $errors );
		}
		return array(
			'success'       => false,
			'error_message' => $error_message,
		);
	}

	/**
	 * @param array $item_ids
	 *
	 * @return array
	 */
	public function lookup( $item_ids ) {
		return $this->find_items(
			self::OPERATION_GET_ITEMS,
			array(
				'ItemIds' => $item_ids,
			)
		);
	}

	/**
	 * @param string $search_index
	 * @param string $keywords
	 * @param int $item_page
	 *
	 * @return array
	 */
	public function search( $search_index, $keywords, $item_page = 1 ) {
		$params = array(
			'Keywords' => $keywords,
			'ItemPage' => intval( $item_page ),
		);
		if ( ! empty( $search_index ) ) {
			$params['SearchIndex'] = $search_index;
		}

		return $this->find_items( self::OPERATION_SEARCH_ITEMS, $params );
	}

	/**
	 * @param string $operation
	 * @param array $params
	 *
	 * @return array
	 */
	private function find_items( $operation, $params ) {
		$request_params = array(
			'PartnerTag'  => $this->associate_tag,
			'PartnerType' => 'Associates',
			'Resources'   => array(
				'ItemInfo.Title',
				'ItemInfo.Classifications',
				'ItemInfo.ByLineInfo',
				'ItemInfo.ContentInfo',
				'ItemInfo.ProductInfo',
				'ItemInfo.ExternalIds',
				'Images.Primary.Small',
				'Images.Primary.Medium',
				'Images.Primary.Large',
				'BrowseNodeInfo.WebsiteSalesRank',
				'Offers.Listings.Price',
			),
		);

		foreach ( $params as $key => $value ) {
			$request_params[ $key ] = $value;
		}

		$payload = json_encode( $request_params );
		$path    = '/paapi5/' . strtolower( $operation );

		$aws = new Amazonjs_Amazon_AwsV4( $this->access_key_id, $this->secret_access_key );
		$aws->setRegionName( $this->region );
		$aws->setServiceName( 'ProductAdvertisingAPI' );
		$aws->setPath( $path );
		$aws->setPayload( $payload );
		$aws->setRequestMethod( 'POST' );
		$aws->addHeader( 'content-encoding', 'amz-1.0' );
		$aws->addHeader( 'content-type', 'application/json; charset=utf-8' );
		$aws->addHeader( 'host', $this->host );
		$aws->addHeader( 'x-amz-target', 'com.amazon.paapi5.v1.ProductAdvertisingAPIv1.' . $operation );
		$headers = $aws->getHeaders();

		$response = wp_remote_request(
			'https://' . $this->host . $path,
			array(
				'method'  => 'POST',
				'headers' => $headers,
				'body'    => $payload,
				'timeout' => 60,
			)
		);

		if ( is_wp_error( $response ) ) {
			return self::wp_error_to_result( $response );
		}

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid Response', $this->text_domain ),
			);
		}

		$r = json_decode( $body, true );

		if ( self::is_error_response( $r ) ) {
			return self::api_error_to_result( $r );
		}

		$operation_result = array();
		foreach ( $r as $key => $value ) {
			if ( preg_match( '/Result$/i', $key ) ) {
				$operation_result = $value;
				break;
			}
		}
		if ( empty( $operation_result ) ) {
			return array(
				'success' => false,
				'message' => __( 'Invalid Response', $this->text_domain ),
			);
		}

		if ( isset( $operation_result['TotalResultCount'] ) ) {
			$page_index                  = isset( $params['ItemPage'] ) ? intval( $params['ItemPage'] ) : 1;
			$open_search['totalResults'] = $operation_result['TotalResultCount'];
			$open_search['startIndex']   = self::SEARCH_ITEMS_DEFAULT_ITEM_COUNT * ( $page_index - 1 ) + 1;
			$open_search['startPage']    = $page_index;
			$open_search['totalPages']   = intval( ceil( $operation_result['TotalResultCount'] / self::SEARCH_ITEMS_DEFAULT_ITEM_COUNT ) );
			$open_search['itemsPerPage'] = self::SEARCH_ITEMS_DEFAULT_ITEM_COUNT;
		} else {
			$open_search['totalResults'] = count( $operation_result['Items'] );
			$open_search['startIndex']   = 1;
			$open_search['startPage']    = 1;
			$open_search['totalPages']   = 1;
			$open_search['itemsPerPage'] = count( $operation_result['Items'] );
		}

		return array(
			'success'   => true,
			'operation' => $operation,
			'os'        => $open_search,
			'items'     => $operation_result['Items'],
		);
	}

	/**
	 * @param array $response
	 *
	 * @return bool
	 */
	protected static function is_error_response( $response ) {
		if ( isset( $response['Errors'] ) ) {
			return true;
		}
		if ( isset( $response['__type'] ) ) {
			if ( preg_match( '/Failure$/', $response['__type'] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array $error_response
	 *
	 * @return array
	 */
	protected static function api_error_to_result( $error_response ) {
		$error_type = '';
		$messages   = array();

		if ( isset( $error_response['__type'] ) ) {
			$error_type = $error_response['__type'];
			$messages[] = $error_type;
		}
		if ( isset( $error_response['message'] ) ) {
			$messages[] = $error_response['message'];
		}
		if ( isset( $error_response['Errors'] ) ) {
			if ( is_array( $error_response['Errors'] ) ) {
				foreach ( $error_response['Errors'] as $error ) {
					$messages[] = $error['Code'] . ': ' . $error['Message'];
				}
			}
		}

		return array(
			'success'       => false,
			'error_code'    => $error_type,
			'error_message' => implode( '<br/>', $messages ),
			'_response'     => $error_response,
		);
	}
}
