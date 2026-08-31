<?php
/**
 * Guestbook entry status
 *
 * @package Inskrift
 */

declare(strict_types=1);

namespace Inskrift\Guestbook;

enum EntryStatus: string {
	case Pending  = 'pending';
	case Approved = 'approved';
	case Spam     = 'spam';
}
