<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Mailer;

enum AttachmentType
{
    case AttachFromPath;
    case AttachFromContent;
    case EmbedFromPath;
    case EmbedFromContent;
}
