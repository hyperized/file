<?php declare(strict_types=1);

use Hyperized\File\Exceptions\LookupFailed;
use Hyperized\File\Runtime\Group;
use Hyperized\File\Runtime\User;

final readonly class FixtureRuntimeUser extends User
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }
}

final readonly class FixtureRuntimeGroup extends Group
{
    public function __construct(int $id)
    {
        parent::__construct($id);
    }
}

it('throws when looking up the name of a missing uid', function (): void {
    (new FixtureRuntimeUser(0x6FFFFFFF))->name();
})->throws(LookupFailed::class);

it('throws when looking up the name of a missing gid', function (): void {
    (new FixtureRuntimeGroup(0x6FFFFFFF))->name();
})->throws(LookupFailed::class);
