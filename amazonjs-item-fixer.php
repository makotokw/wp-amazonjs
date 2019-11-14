<?php

class Amazonjs_Itemfixer {
	public static function fixed_item( &$item ) {
		self::fixed_item_browsenodeinfo( $item );
		self::fixed_item_iteminfo( $item );
		self::fixed_item_image( $item );
		self::fixed_item_by_offers( $item );
	}

	private static function fixed_item_browsenodeinfo( &$item ) {
		if ( ! isset( $item['BrowseNodeInfo'] ) ) {
			return;
		}

		if ( isset( $item['BrowseNodeInfo']['WebsiteSalesRank'] ) ) {
			$r = $item['BrowseNodeInfo']['WebsiteSalesRank'];
			if ( isset( $r['SalesRank'] ) ) {
				$item['SalesRank'] = $r['SalesRank'];
			}
		}
		unset( $item['BrowseNodeInfo'] );
	}

	private static function fixed_item_iteminfo( &$item ) {
		if ( ! isset( $item['ItemInfo'] ) ) {
			return;
		}
		$ii = $item['ItemInfo'];

		foreach ( array( 'Title' ) as $key ) {
			$display_value = self::find_display_value( $ii, $key );
			if ( $display_value ) {
				$item[ $key ] = $display_value;
			}
		}

		foreach (
			array(
				'ByLineInfo'      => array( 'Brand', 'Manufacturer', 'Contributors' ),
				'Classifications' => array( 'Binding', 'ProductGroup' ),
				'ContentInfo'     => array( 'PagesCount', 'PublicationDate' ),
				'ExternalIds'     => array( 'EANs', 'ISBNs' ),
				'ProductInfo'     => array( 'ReleaseDate' ),
			) as $group => $keys
		) {
			if ( ! isset( $ii[ $group ] ) ) {
				continue;
			}
			foreach ( $keys as $key ) {
				if ( 'Contributors' === $key ) {
					if ( isset( $ii[ $group ][ $key ] ) ) {
						$value = self::extract_name_from_contributes( $ii[ $group ][ $key ] );
						if ( ! empty( $value ) ) {
							$item['Creator'] = $value;
						}
					}
					continue;
				}

				if ( 'ExternalIds' === $group ) {
					if ( isset( $ii[ $group ][ $key ] ) ) {
						self::fixed_item_external_id( $item, $ii[ $group ][ $key ] );
					}
					continue;
				}

				$value = self::find_display_value( $ii[ $group ], $key );
				if ( $value ) {
					if ( 'PublicationDate' === $key || 'ReleaseDate' === $key ) {
						try {
							$d     = new DateTime( $value );
							$value = $d->format( 'Y-m-d' );
						} catch ( Throwable $e ) {
						}
					} elseif ( 'PagesCount' === $key ) {
						$key = 'NumberOfPages';
					}
					$item[ $key ] = $value;
					continue;
				}
			}
		}
		unset( $item['ItemInfo'] );
	}

	/**
	 * @param array $offer_item
	 *
	 * @return string
	 */
	private static function fixed_condition_value( $offer_item ) {
		if ( isset( $offer_item['Condition'] ) && $offer_item['Condition']['Value'] ) {
			return $offer_item['Condition']['Value'];
		}

		return '';
	}

	private static function fixed_item_image( &$item ) {
		if ( ! isset( $item['Images'] ) ) {
			return;
		}

		if ( isset( $item['Images']['Primary'] ) ) {
			foreach ( $item['Images']['Primary'] as $size => $value ) {
				$item[ $size . 'Image' ] = [
					'src'    => $value['URL'],
					'width'  => $value['Width'],
					'height' => $value['Height'],
				];
			}
		}
		unset( $item['Images'] );
	}

	private static function fixed_item_by_offers( &$item ) {
		if ( ! isset( $item['Offers'] ) ) {
			return;
		}
		$o = $item['Offers'];

		if ( isset( $o['Listings'] ) ) {
			foreach ( $o['Listings'] as $l ) {
				$lp = $l['Price'];
				if ( isset( $lp['DisplayAmount'] ) ) {
					$lp['FormattedPrice'] = $lp['DisplayAmount'];
					unset( $lp['DisplayAmount'] );
				}
				$item['ListPrice'] = $lp;
				break;
			}
		}
		unset( $item['Offers'] );
	}

	private static function find_display_value( $item, $key ) {
		if ( ! is_array( $item ) ) {
			return null;
		}
		if ( ! isset( $item[ $key ] ) ) {
			return null;
		}
		if ( ! is_array( $item[ $key ] ) ) {
			return null;
		}
		if ( isset( $item[ $key ]['DisplayValue'] ) ) {
			return $item[ $key ]['DisplayValue'];
		}

		return null;
	}

	private static function extract_name_from_contributes( $contributes ) {
		$values = array();
		foreach ( $contributes as $c ) {
			$values[] = $c['Name'];
		}
		$values = array_unique( $values );

		return array_values( $values );
	}

	private static function fixed_item_external_id( &$item, $value ) {
		if ( ! isset( $value['Label'] ) || ! isset( $value['DisplayValues'] ) ) {
			return;
		}
		if ( is_array( $value['DisplayValues'] ) && ! empty( $value['DisplayValues'] ) ) {
			$item[ $value['Label'] ] = $value['DisplayValues'][0];
		}
	}
}
