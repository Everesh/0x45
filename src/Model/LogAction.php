<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

enum LogAction: string
{
    case PostCreated = "post_created";
    case PostPatched = "post_patched";
    case PostDeleted = "post_deleted";
    case PostSeen = "post_seen";
}
