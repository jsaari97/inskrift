<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

const INSKRIFT_PLUGIN_DIR = '';

abstract class WP_UnitTestCase extends TestCase {
	/**
	 * Runs before each test.
	 */
	public function set_up(): void {}

	/**
	 * Runs after each test.
	 */
	public function tear_down(): void {}
}

/**
 * @param callable(): void $callback
 */
function tests_add_filter( string $hook_name, callable $callback ): void {
}
