<?php

declare(strict_types=1);

namespace Everesh\ZeroX45\Model;

enum LogAction: string
{
    case TopicCreated = "topic_created";
    case PostCreated = "post_created";
    case PostEdited = "post_edited";
    case PostDeleted = "post_deleted";
    // no thread actions, if its a thread post can be inffered via thread$anchor_id join if needed
}
