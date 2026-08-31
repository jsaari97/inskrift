<?php
/**
 * Guestbook entry
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Guestbook;

use DateTimeImmutable;

/**
 * Contains one guestbook entry.
 */
final readonly class Entry {
	/**
	 * Creates an entry.
	 *
	 * @param int               $id         Database ID.
	 * @param string            $name       Visitor name.
	 * @param string            $message    Visitor message.
	 * @param EntryStatus       $status     Moderation status.
	 * @param DateTimeImmutable $created_at Creation date in UTC.
	 */
	public function __construct(
		public int $id,
		public string $name,
		public string $message,
		public EntryStatus $status,
		public DateTimeImmutable $created_at,
	) {}
}
