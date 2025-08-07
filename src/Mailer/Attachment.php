<?php

declare(strict_types=1);

namespace PhoneBurner\Pinch\Component\Mailer;

readonly class Attachment
{
    public AttachmentType $type;

    public function __construct(
        public string $path = '',
        public string $content = '',
        public string|null $name = null,
        public string|null $content_type = null,
        public bool $inline = false,
    ) {
        $this->type = match (true) {
            $this->path !== '' => match (true) {
                $this->content !== '' => throw new \InvalidArgumentException(
                    'Attachment cannot have both a file path and content',
                ),
                $this->inline => AttachmentType::EmbedFromPath,
                default => AttachmentType::AttachFromPath,
            },
            $this->content !== '' => match (true) {
                $this->inline => AttachmentType::EmbedFromContent,
                default => AttachmentType::AttachFromContent,
            },
            default => throw new \InvalidArgumentException(
                'Attachment must have a file path or content',
            ),
        };
    }

    public static function fromPath(
        string $path,
        string|null $name = null,
        string|null $content_type = null,
        bool $inline = false,
    ): self {
        return new self(path: $path, name: $name, content_type: $content_type, inline: $inline);
    }

    public static function fromContent(
        string $content,
        string|null $name = null,
        string|null $content_type = null,
        bool $inline = false,
    ): self {
        return new self(content: $content, name: $name, content_type: $content_type, inline: $inline);
    }
}
