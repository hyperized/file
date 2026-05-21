<?php declare(strict_types=1);

namespace Hyperized\File\Reconcile;

enum ChangeKind: string
{
    case Created = 'created';
    case ModeChanged = 'mode_changed';
    case OwnerChanged = 'owner_changed';
    case GroupChanged = 'group_changed';
    case Retargeted = 'retargeted';
    case Removed = 'removed';
    case ContentWritten = 'content_written';
}
