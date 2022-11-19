<?php

namespace ACFImproved;

class Data {

	public function __construct() {}

	public static function clearOptionGroupCache(string $option) {
		self::get_option($option, true);
	}

	/**
	 * Get option
	 *
	 * This function is an improved version of ACF's get_field('my_field', 'option').
	 * ACF's version creates a new database query for each option and child option.
	 * This function fetches all options within a group in a single database query.
	 * The more complex your option groups are, the greater performance benefit you will get from this.
	 * Makes use of object caching for even greater performance benefits.
	 * If persistent object caching is enabled on the server,
	 *   a database query will only be performed when an option group is updated.
	 *
	 * @param string $group
	 * @param bool $force_refresh
	 *
	 * @return array|false|mixed
	 */
	public static function get_option(string $group = '', bool $force_refresh = false) {

		if ( empty($group) ) {
			return [];
		}

		// Format option_name string

		$option_name = 'options_' . filter_var($group, FILTER_SANITIZE_STRING) . '_';

		// Check for existing value in WordPress object cache

		$fields = wp_cache_get($option_name, 'options');

		// If no existing cache value or force refresh is true, fetch from database

		if ( $force_refresh || !$fields ) {

			// Connect to WordPress database and prepare/execute query

			global $wpdb;
			$table = $wpdb->prefix . 'options';
			$sql = "SELECT option_name, option_value FROM $table WHERE option_name LIKE CONCAT('%s', '%')";
			$results = $wpdb->get_results($wpdb->prepare($sql, $option_name));

			if ( is_array($results) && empty($wpdb->last_error) ) {

				// Format output array

				$fields = [];

				foreach ( $results as $result ) {
					$key = str_replace($option_name, '', $result->option_name);
					$fields[$key] = $result->option_value;
				}

				$fields = self::format_option_group($fields);

				// Update cache value

				wp_cache_set($option_name, $fields, 'options');
			}
		}

		return $fields;
	}

	/**
	 * Format option group
	 *
	 * This function transforms the data structure from a single dimensional array storing groups like [ my_group, my_group_subfield ]
	 * into the same format ACF uses like $fields['my_group']['subfield'].
	 *
	 * First we get all array keys where the value is an empty string since an empty string could represent a group field.
	 * Then check each of these keys against the full array of fields to find any child fields.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	public static function format_option_group(array $fields): array {

		$final_array = [];
		$field_keys = array_keys($fields);

		foreach ( $field_keys as $k => $field_key ) {

			if ( $fields[$field_key] !== '' && $fields[$field_key] !== [] ) {
				$final_array[$field_key] = $fields[$field_key];
				continue;
			}

			$child_fields = self::find_child_fields($field_key, $fields);

			if ( !empty($child_fields) ) {
				$final_array[$field_key] = $child_fields;
			}
		}

		return $final_array;
	}

	/**
	 * Find child fields
	 *
	 * Recursive function to find and map child fields for unlimited levels of nested option groups.
	 *
	 * @param string $key
	 * @param array $fields
	 * @param int $start
	 *
	 * @return array
	 */
	public static function find_child_fields(string $parent_key, array $fields, int $start = 0): array {

		$results = [];

		// Since the fields array is sorted in a way that puts the group field before any child fields,
		// we don't need to check any array keys before the group field itself.
		$fields = array_slice($fields, $start);
		$field_keys = array_keys($fields);

		foreach ( $field_keys as $k => $field_key ) {

			if ( $field_key === $parent_key ) {
				// Prevent infinite loop if parent key is passed as a child key to this function.
				continue;
			}
			else if ( $fields[$field_key] === '' && strpos($field_key, $parent_key) !== false ) {
				// Key contains parent e.g. my_group_title contains my_group so this is a child field.
				// Now we use recursion to check if
				$results[$field_key] = self::find_child_fields($field_key, $fields, $k);
			}
			else if ( $fields[$field_key] !== '' && $fields[$field_key] !== [] && strpos($field_key, $parent_key) !== false ) {
				$new_field_key = trim(
					str_replace($parent_key, '', $field_key),
					'_'
				);
				$results[$new_field_key] = $fields[$field_key];
			}

		}

		return $results;
	}

}